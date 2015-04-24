<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category edit general tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Category_Tab_Seo extends Mage_Adminhtml_Block_Catalog_Form {

	protected $_category;

	public function __construct() {
		parent::__construct();
		$this->setShowGlobalIcon(true);

		//database read adapter
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		//database table prefix
		$this->tablePrefix = (string) Mage::getConfig()->getTablePrefix();

		$this->storeID = Mage::app()->getStore()->getStoreId();
	}

	public function getCategory() {
		if (!$this->_category) {
			$this->_category = Mage::registry('category');
		}
		return $this->_category;
	}

	public function _prepareLayout() {
		parent::_prepareLayout();
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('_nps_seo');
		$form->setDataObject($this->getCategory());
		$fieldset = $form->addFieldset('seo_fieldset', array('legend' => Mage::helper('catalog')->__('Category SEO')));

		//check if new category
		if (!$this->getCategory()->getId()) {
			/*
			$fieldset->addField('path', 'select', array(
			'name'  => 'path',
			'label' => Mage::helper('catalog')->__('Parent Category'),
			'value' => base64_decode($this->getRequest()->getParam('parent')),
			'values'=> $this->_getParentCategoryOptions(),
			//'required' => true,
			//'class' => 'required-entry'
			),
			'name'
			);*/

			//get parent
			$parentId = $this->getRequest()->getParam('parent');
			if (!$parentId) {
				$parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
			}
			//set hidden path field
			$fieldset->addField('path', 'hidden', array(
				'name' => 'path',
				'value' => $parentId,
			));
			$fieldset->addField('_is_new', 'hidden', array(
				'name' => 'seo_is_new',
				'value' => 1,
			));

			//set category SEO values as default
			$seoValues = $this->_getCategorySEODefaults();

		} else {
			//set category ID
			$fieldset->addField('id', 'hidden', array(
				'name' => 'id',
				'value' => $this->getCategory()->getId(),
			));
			//set hidden path field
			$fieldset->addField('path', 'hidden', array(
				'name' => 'path',
				'value' => $this->getCategory()->getPath(),
			));

			//set category ID
			$fieldset->addField('seo_category_id', 'hidden', array(
				'name' => 'seo_category_id',
				'value' => $this->getCategory()->getId(),
			));
			//check if there is an SEO record
			if ($this->_checkIfExisting($this->getCategory()->getId())) {
				$fieldset->addField('_is_new', 'hidden', array(
					'name' => 'seo_is_new',
					'value' => 0,
				));
			} else {
				$fieldset->addField('_is_new', 'hidden', array(
					'name' => 'seo_is_new',
					'value' => 1,
				));
			}

			//get category SEO values
			$seoValues = $this->_getCategorySEOSettings($this->getCategory()->getId());
		}

		//SEO Category Type
		$fieldset->addField('_category_type', 'select', array(
			'label' => 'Category Type',
			//'required' => ( $seoValues['is_new'] ? false : true),
			'name' => 'seo_category_type',
			'values' => array('' => '--SELECT TYPE--', 'distinct' => 'Distinct Category', 'redirect' => 'Redirect', 'canonical' => 'Canonical'),
			'value' => $seoValues['category_type'],
			'class' => 'nps-settings-options-controller',
			'onchange' => "enableApplicableSeoValues(jQuery(this).find(':selected').val())",
			'after_element_html' => '<p class="field-note">Select how you would like the system to handle traffic.</p><ul class="field-note"><li>Redirect<ul><li>Forwards the user to the new page</li></ul></li><li>Canonical<ul><li>Informs all robots crawling the page (Google) that the page is a duplicate of another page on the site.</li></ul></li><li>Distinct<ul><li>Informs the system that this category is unlike any other on the site</li></ul></li></ul>',
		));

		// options if distinct category
		// is primary
		$fieldset->addField('_is_primary', 'checkbox', array(
			'label' => 'Primary Category',
			'name' => 'seo_is_primary',
			'checked' => ($seoValues['is_primary'] ? true : false),
		));

		// primary category note
		//$fieldset->addField('_seo_category_note1', 'note', array('text' => '<p class="field-note"></p>'));

		// options if redirect or canonical category
		//SEO Is Child Category
		$fieldset->addField('_is_child', 'checkbox', array(
			'label' => 'Is Dependent',
			'name' => 'seo_is_child',
			'checked' => ($seoValues['is_child'] ? true : false),
			'disabled' => 'true',
			'after_element_html' => '',
		));

		//SEO Child ID
		$fieldset->addField('_parent_id', 'text', array(
			'label' => 'Redirect / Canonical Category ID',
			'name' => 'seo_parent_id',
			'value' => $seoValues['parent_id'],
			'disabled' => 'true',
		));

		//SEO Maintain General Info
		$fieldset->addField('_gen_info', 'checkbox', array(
			'label' => 'Maintain General Info',
			'name' => 'seo_gen_info',
			'checked' => ($seoValues['gen_info'] ? true : false),
			'disabled' => 'true',
		));

		//SEO Maintain Design Info
		$fieldset->addField('_design_info', 'checkbox', array(
			'label' => 'Maintain Design Info',
			'name' => 'seo_design_info',
			'checked' => ($seoValues['design_info'] ? true : false),
			'disabled' => 'true',
		));

		//SEO Maintain Display Info
		$fieldset->addField('_display_info', 'checkbox', array(
			'label' => 'Maintain Display Info',
			'name' => 'seo_display_info',
			'checked' => ($seoValues['display_info'] ? true : false),
			'disabled' => 'true',
		));

		//SEO Maintain Breadcrumb Info
		$fieldset->addField('_breadcrumb', 'checkbox', array(
			'label' => 'Maintain Breadcrumb For This Category',
			'name' => 'seo_breadcrumb',
			'checked' => ($seoValues['breadcrumb'] ? true : false),
			'disabled' => 'true',
		));

		//SEO Redirect Type
		$fieldset->addField('_redirect_type', 'select', array(
			'label' => 'Redirect Type',
			'name' => 'seo_redirect_type',
			'values' => array('', '301' => '301 (permament)', '302' => '302 (temporary)'),
			'value' => $seoValues['redirect_type'],
			'disabled' => 'true',
		));

		//SEO Canonical Checkbox
		/*
		$fieldset->addField('seo_redirect_type', 'select', array(
		'label' => 'Redirect Type',
		'name' => 'seo_redirect_type',
		'values' => array('301','302'),
		'before_element_html' => '<div id="seo-redirect-options">',
		'after_element_html' => '</div>',
		));
		 */

		//$this->_setFieldset($this->getCategory()->getAttributes(true), $fieldset);

		$form->addValues($this->getCategory()->getData());

		$form->setFieldNameSuffix('general');
		$this->setForm($form);
	}

	protected function _getAdditionalElementTypes() {
		return array(
			'image' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_category_helper_image'),
		);
	}

	protected function _getParentCategoryOptions($node = null, &$options = array()) {
		if (is_null($node)) {
			$node = $this->getRoot();
		}
		if ($node) {
			$options[] = array(
				'value' => $node->getPathId(),
				'label' => str_repeat('&nbsp;', max(0, 3 * ($node->getLevel()))) . $this->escapeHtml($node->getName()),
			);

			foreach ($node->getChildren() as $child) {
				$this->_getParentCategoryOptions($child, $options);
			}
		}
		return $options;
	}
	public static function _getCategorySEODefaults($category_id = null, $overwrites = array()) {
		$defaults = array(
			'category_id' => $category_id,
			'category_type' => '',
			'is_primary' => 0,
			'parent_id' => null,
			'redirect' => 0,
			'redirect_type' => null,
			'canonical' => 0,
			'gen_info' => 0,
			'design_info' => 0,
			'display_info' => 0,
			'is_child' => 0,
			'breadcrumb' => 0,
			'is_new' => 1,
		);

		return $defaults;
	}
	protected function _getCategorySEOSettings($category_id) {
		$query = "SELECT `id`,`category_id`,`category_type`,`is_primary`,`parent_id`,`redirect`,`redirect_type`,`canonical`,`gen_info`,`design_info`,`display_info`,`is_child`,`breadcrumb` FROM `catalog_category_seo` WHERE `category_id` = " . $category_id;
		$this->sqlread->query($query);
		$results = $this->sqlread->fetchRow($query);
		if (!$results) {
			$results = $this->_getCategorySEODefaults($category_id);
		} else {
			//add is new false to the array
			$results['is_new'] = 0;
		}

		return $results;
	}
	protected function _checkIfExisting($category_id) {
		$query = "SELECT * FROM catalog_category_seo WHERE category_id = " . $category_id;
		$this->sqlread->query($query);
		$results = $this->sqlread->fetchRow($query);
		if (!$results) {
			$results = false;
		} else {
			return true;
		}
	}

}
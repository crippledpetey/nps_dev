<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    HALOTHEMES
 * @package     HALO_Filterproducts
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HALO_Filterproducts_Block_List
 extends Mage_Catalog_Block_Product_Abstract
 implements Mage_Widget_Block_Interface {

	/**
	 * A model to serialize attributes
	 * @var Varien_Object
	 */
	/**
	 * Default toolbar block name
	 *
	 * @var string
	 */
	protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';

	/**
	 * Product Collection
	 *
	 * @var Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected $_productCollection;
	protected $size = 0;
	/**
	 * Initialization
	 */
	protected function _construct() {
		$this->_serializer = new Varien_Object();
		//$this->addData('type','computer');
		parent::_construct();
	}

	/**
	 * Produces links list html
	 *
	 * @return string
	 */

	/**
	 * Retrieve current view mode
	 *
	 * @return string
	 */
	public function getMode() {
		return $this->getChild('toolbar')->getCurrentMode();
	}

	/**
	 * Retrieve list toolbar HTML
	 *
	 * @return string
	 */

	public function setCollection($collection) {
		$this->_productCollection = $collection;
		return $this;
	}

	public function addAttribute($code) {
		$this->_getProductCollection()->addAttributeToSelect($code);
		return $this;
	}

	public function getPriceBlockTemplate() {
		return $this->_getData('price_block_template');
	}

	/**
	 * Retrieve Catalog Config object
	 *
	 * @return Mage_Catalog_Model_Config
	 */
	protected function _getConfig() {
		return Mage::getSingleton('catalog/config');
	}

	protected function _initReviewsHelperBlock() {
		if (!$this->_reviewsHelperBlock) {
			if (!Mage::helper('catalog')->isModuleEnabled('Mage_Review')) {
				return false;
			} else {
				$this->_reviewsHelperBlock = $this->getLayout()->createBlock('review/helper');
			}
		}
		return true;
	}

	protected function getProductCollection() {
		$typeFilter = $this->getData('type_filter');
		$storeId = Mage::app()->getStore()->getId();
		if ($typeFilter == 'bestseller') {
			$products = Mage::getResourceModel('reports/product_collection')
				->addOrderedQty()
				->setStoreId($storeId)
				->addStoreFilter($storeId)
				->setOrder('ordered_qty', 'desc');
		} else if ($typeFilter == 'halo_featured') {
			$products = Mage::getModel('catalog/product')->getCollection()
			                                             ->addAttributeToFilter('halo_featured', 1);

		} else if ($typeFilter == 'most_viewed') {
			$products = Mage::getResourceModel('reports/product_collection')
				->addOrderedQty()
				->setStoreId($storeId)
				->addStoreFilter($storeId)
				->addViewsCount();
		} else if ($typeFilter == 'new_products') {
			$todayStartOfDayDate = Mage::app()->getLocale()->date()
			                                  ->setTime('00:00:00')
			                                  ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$todayEndOfDayDate = Mage::app()->getLocale()->date()
			                                ->setTime('23:59:59')
			                                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$products = Mage::getResourceModel('catalog/product_collection');
			$products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

			$products->addStoreFilter()
			         ->addAttributeToFilter('news_from_date', array('or' => array(
				         0 => array('date' => true, 'to' => $todayEndOfDayDate),
				         1 => array('is' => new Zend_Db_Expr('null'))),
			         ), 'left')
			         ->addAttributeToFilter('news_to_date', array('or' => array(
				         0 => array('date' => true, 'from' => $todayStartOfDayDate),
				         1 => array('is' => new Zend_Db_Expr('null'))),
			         ), 'left')
			         ->addAttributeToFilter(
				         array(
					         array('attribute' => 'news_from_date', 'is' => new Zend_Db_Expr('not null')),
					         array('attribute' => 'news_to_date', 'is' => new Zend_Db_Expr('not null')),
				         )
			         )
			         ->addAttributeToSort('news_from_date', 'desc');

		} else if ($typeFilter == 'sale_off') {
			$todayStartOfDayDate = Mage::app()->getLocale()->date()
			                                  ->setTime('00:00:00')
			                                  ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$todayEndOfDayDate = Mage::app()->getLocale()->date()
			                                ->setTime('23:59:59')
			                                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$products = Mage::getResourceModel('catalog/product_collection');
			$products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

			$products->addStoreFilter()
			         ->addAttributeToFilter('special_price', array('gt' => 0))
			         ->addAttributeToFilter('special_from_date', array('or' => array(
				         0 => array('date' => true, 'to' => $todayEndOfDayDate),
				         1 => array('is' => new Zend_Db_Expr('null'))),
			         ), 'left')
			         ->addAttributeToFilter('special_to_date', array('or' => array(
				         0 => array('date' => true, 'from' => $todayStartOfDayDate),
				         1 => array('is' => new Zend_Db_Expr('null'))),
			         ), 'left')
			         ->addAttributeToFilter(
				         array(
					         array('attribute' => 'special_from_date', 'is' => new Zend_Db_Expr('not null')),
					         array('attribute' => 'special_to_date', 'is' => new Zend_Db_Expr('not null')),
				         )
			         );
		} else if ($typeFilter == 'homepage_feat') {
			$products = Mage::getModel('catalog/product')->getCollection()
			                                             ->addAttributeToFilter('homepage_featured', 1);
		} else {
			$products = Mage::getModel('catalog/product')->getCollection();
		}

		if ($category = $this->getData('category')) {
			$products->addCategoryFilter(Mage::getModel('catalog/category')->load(str_replace('category/', '', $category)));
		}
		$this->_addProductAttributesAndPrices($products);

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		/* For category page */
		if (Mage::registry('current_category')) {
			$products->addUrlRewrite(Mage::registry('current_category')->getId());
		}

		$pageSize = $this->getData('limit_count');
		if (isset($pageSize)) {
			$products->setPageSize($pageSize);
		}
		$products->setCurPage($this->getRequest()->getParam('p', 1));

		$this->setCollection($products);

		$this->setSize(count($this->_productCollection->getData()));

		return $this->_productCollection;
	}

	protected function _toHtml() {
		if ($this->getData('template') == 'custom_template') {$this->setTemplate($this->getData('custom_theme'));}
		return parent::_toHtml();
	}

	public function getToolbarHtml() {
		$this->setToolbar($this->getLayout()->createBlock('catalog/product_list_toolbar', 'Toolbar'));
		$toolbar = $this->getToolbar();
		$toolbar->enableExpanded();
		$toolbar->setAvailableOrders(array(
			        'ordered_qty' => $this->__('Most Purchased'),
			        'name' => $this->__('Name'),
			        'price' => $this->__('Price'),
		        ))
		        ->setDefaultOrder('ordered_qty')
		        ->setDefaultDirection('desc')
		        ->setCollection($this->_productCollection);

		$pager = $this->getLayout()->createBlock('page/html_pager', 'Pager');
		$pager->setCollection($this->_productCollection);
		$toolbar->setChild('product_list_toolbar_pager', $pager);
		return $toolbar->_toHtml();
	}

	public function getThumbnailWidth() {
		$value = $this->getData('thumbnail_width');
		if (!is_numeric($value)) {
			$value = 150;
		}

		return $value;
	}

	public function getThumbnailHeight() {
		$value = $this->getData('thumbnail_height');
		if (!is_numeric($value)) {
			$value = 150;
		}

		return $value;
	}

	public function getColumnCount() {
		return $this->getData('column_count');
	}

	public function setSize($size) {
		$this->size = $size;
	}

	public function getSize() {
		return $this->size;
	}
}

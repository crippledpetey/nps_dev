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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product list
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_Abstract {
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

	public function __construct() {
		parent::__construct();
		//sql connector
		$this->resource = Mage::getSingleton('core/resource');
		$this->readConnection = $this->resource->getConnection('core_read');
		$this->writeConnection = $this->resource->getConnection('core_write');
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected function _getProductCollection() {
		if (is_null($this->_productCollection)) {
			$layer = $this->getLayer();
			/* @var $layer Mage_Catalog_Model_Layer */
			if ($this->getShowRootCategory()) {
				$this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
			}

			// if this is a product view page
			if (Mage::registry('product')) {
				// get collection of categories this product is associated with
				$categories = Mage::registry('product')->getCategoryCollection()
				                                       ->setPage(1, 1)
				                                       ->load();
				// if the product is associated with any category
				if ($categories->count()) {
					// show products from this category
					$this->setCategoryId(current($categories->getIterator()));
				}
			}

			$origCategory = null;
			if ($this->getCategoryId()) {
				$category = Mage::getModel('catalog/category')->load($this->getCategoryId());
				if ($category->getId()) {
					$origCategory = $layer->getCurrentCategory();
					$layer->setCurrentCategory($category);
					$this->addModelTags($category);
				}
			}
			$this->_productCollection = $layer->getProductCollection();

			$this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

			if ($origCategory) {
				$layer->setCurrentCategory($origCategory);
			}
		}

		return $this->_productCollection;
	}

	/**
	 * Get catalog layer model
	 *
	 * @return Mage_Catalog_Model_Layer
	 */
	public function getLayer() {
		$layer = Mage::registry('current_layer');
		if ($layer) {
			return $layer;
		}
		return Mage::getSingleton('catalog/layer');
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	public function getLoadedProductCollection() {
		return $this->_getProductCollection();
	}

	/**
	 * Retrieve current view mode
	 *
	 * @return string
	 */
	public function getMode() {
		return $this->getChild('toolbar')->getCurrentMode();
	}

	/**
	 * Need use as _prepareLayout - but problem in declaring collection from
	 * another block (was problem with search result)
	 */
	protected function _beforeToHtml() {
		$toolbar = $this->getToolbarBlock();

		// called prepare sortable parameters
		$collection = $this->_getProductCollection();

		// use sortable parameters
		if ($orders = $this->getAvailableOrders()) {
			$toolbar->setAvailableOrders($orders);
		}
		if ($sort = $this->getSortBy()) {
			$toolbar->setDefaultOrder($sort);
		}
		if ($dir = $this->getDefaultDirection()) {
			$toolbar->setDefaultDirection($dir);
		}
		if ($modes = $this->getModes()) {
			$toolbar->setModes($modes);
		}

		// set collection to toolbar and apply sort
		$toolbar->setCollection($collection);

		$this->setChild('toolbar', $toolbar);
		Mage::dispatchEvent('catalog_block_product_list_collection', array(
			'collection' => $this->_getProductCollection(),
		));

		$this->_getProductCollection()->load();

		return parent::_beforeToHtml();
	}

	/**
	 * Retrieve Toolbar block
	 *
	 * @return Mage_Catalog_Block_Product_List_Toolbar
	 */
	public function getToolbarBlock() {
		if ($blockName = $this->getToolbarBlockName()) {
			if ($block = $this->getLayout()->getBlock($blockName)) {
				return $block;
			}
		}
		$block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
		return $block;
	}

	/**
	 * Retrieve additional blocks html
	 *
	 * @return string
	 */
	public function getAdditionalHtml() {
		return $this->getChildHtml('additional');
	}

	/**
	 * Retrieve list toolbar HTML
	 *
	 * @return string
	 */
	public function getToolbarHtml() {
		return $this->getChildHtml('toolbar');
	}

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

	/**
	 * Prepare Sort By fields from Category Data
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @return Mage_Catalog_Block_Product_List
	 */
	public function prepareSortableFieldsByCategory($category) {
		if (!$this->getAvailableOrders()) {
			$this->setAvailableOrders($category->getAvailableSortByOptions());
		}
		$availableOrders = $this->getAvailableOrders();
		if (!$this->getSortBy()) {
			if ($categorySortBy = $category->getDefaultSortBy()) {
				if (!$availableOrders) {
					$availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
				}
				if (isset($availableOrders[$categorySortBy])) {
					$this->setSortBy($categorySortBy);
				}
			}
		}

		return $this;
	}

	/**
	 * Retrieve block cache tags based on product collection
	 *
	 * @return array
	 */
	public function getCacheTags() {
		return array_merge(
			parent::getCacheTags(),
			$this->getItemsTags($this->_getProductCollection())
		);
	}

	/**
	 * CUSTOM FUNCTIONS
	 *
	 * @return array
	 */
	public function _getImages($product_id, $primary = false) {
		$query = "SELECT `id`,`product_id`,`manu`,`file_name`,`order`, `type`, `title`, `in_gallery`, `default_img` FROM `nps_product_media_gallery` WHERE `product_id` = " . $product_id;
		$query .= " ORDER BY `order`";
		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}
	public function _getChildGalleryImages($product_id) {
		$images = array();
		//get all childs
		$children = $this->_getChildrenProducts($product_id);
		foreach ($children as $kid) {
			$kidImage = $this->_getImages($kid['entity_id']);
			foreach ($kidImage as $key => $row) {
				$images[] = $row;
			}
		}
		return $images;
	}
	public function _convertManuToFolder($manu) {
		return strtolower(str_replace(array(' ', '-', '_'), null, $manu));
	}
	public function _getTitles($product_id) {
		$return = array();
		//get all children
		$all_products = array();
		$kids = $this->_getChildrenProducts($product_id);
		foreach ($kids as $pid => $prd) {
			$product = Mage::getModel('catalog/product')->load($pid);
			$title = $product->getName();
			$manu = $product->getAttributeText('manufacturer');
			$return[$prd['option_type_id']] = array('title' => $title, 'sku' => $prd['sku'], 'manu' => $manu);
		}
		return $return;
	}
	public function _getStockStatus($product_id) {
		$inventory = $this->readConnection->fetchRow("SELECT `qty` FROM `cataloginventory_stock_status` WHERE `product_id` = " . $product_id);
		if (empty($inventory['qty'])) {
			return 0;
		}
		return substr($inventory['qty'], 0, stripos($inventory['qty'], "."));
	}
	protected function _facebookLink($url) {
		return 'https://www.facebook.com/sharer/sharer.php?u=' . htmlspecialchars($url);
	}
	protected function _twitterLink($url) {
		return 'https://twitter.com/home?status=' . htmlspecialchars($url);
	}
	protected function _googleplusLink($url) {
		return 'https://plus.google.com/share?url=' . htmlspecialchars($url);
	}
	protected function _pinterestLink($image_src, $description = null) {
		$base = 'https://pinterest.com/pin/create/button/?url=' . $image_src . '&media=' . $image_src;
		if (!empty($description)) {$base .= '&description=' . $description;}
		return $base;
	}
	public function _generatSocialShareList($url, $image_url = null, $exclude = array()) {
		//start html
		$html = '<div id="product-pg-social-share"><ul>';
		if (!in_array('facebook', $exclude)) {
			$html .= '<li class="social-share-icon" id="shareme-facebook"><a target="_blank" href="' . $this->_facebookLink($url) . '" title="Share this on Facebook"><img src="/media/social/facebook-gray.png" title="Share this on Facebook"></a>';
		}
		if (!in_array('twitter', $exclude)) {
			$html .= '<li class="social-share-icon" id="shareme-twitter"><a target="_blank" href="' . $this->_twitterLink($url) . '" title="Tweet about this"><img src="/media/social/twitter-gray.png" title="Tweet about this"></a>';
		}
		if (!in_array('googleplus', $exclude)) {
			$html .= '<li class="social-share-icon" id="shareme-googleplus"><a target="_blank" href="' . $this->_googleplusLink($url) . '" title="Plus one this"><img src="/media/social/googleplus-gray.png" title="Plus one this"></a>';
		}
		if (!in_array('pinterest', $exclude) && $image_url) {
			$html .= '<li class="social-share-icon" id="shareme-pinterest"><a target="_blank" href="' . $this->_pinterestLink($url) . '" title="Pin this"><img src="/media/social/pinterest-gray.png" title="Pin this"></a>';
		}
		$html .= '</ul></div>';

		return $html;
	}
}

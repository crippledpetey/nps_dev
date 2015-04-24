<?php
/**
CUSTOM DROP PHP FUNCTIONS
 */
class productDrop {

	public function __construct() {
		//database read adapter
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		//database table prefix
		$this->tablePrefix = (string) Mage::getConfig()->getTablePrefix();

		// transfer relation
		//$select = $connection->select()->from($tablePrefix . 'catalog_product_option', array('option_id', 'in_group_id'))->where('product_id = XX AND in_group_id > 65535');
	}

	public function getLayerID($_filter) {
		if ($_filter->getType() == 'catalog/layer_filter_attribute') {
			return $_filter->getAttributeModel()->getAttributeCode();
		}
	}

	public function getContainerProductID($entity_id) {
		$query = "SELECT p.product_id FROM catalog_product_option AS p INNER JOIN catalog_product_option_type_value AS o ON o.option_id = p.option_id INNER JOIN catalog_product_entity AS e ON e.sku = o.sku WHERE e.entity_id = " . $entity_id;
		return $this->sqlread->fetchAll($query);
	}
	public function getChildOptionTypeID($entity_id) {
		$query = "SELECT o.option_type_id FROM catalog_product_option AS p INNER JOIN catalog_product_option_type_value AS o ON o.option_id = p.option_id INNER JOIN catalog_product_entity AS e ON e.sku = o.sku WHERE e.entity_id = " . $entity_id;
		return $this->sqlread->fetchAll($query);
	}
	public function getUrlOptionsForProduct($product_id) {
		$query = "SELECT o.option_type_id as 'npsf', e.entity_id as 'chid', e.sku, t.title FROM catalog_product_option AS p INNER JOIN catalog_product_option_type_value AS o ON o.option_id = p.option_id INNER JOIN catalog_product_entity AS e ON e.sku = o.sku INNER JOIN catalog_product_option_type_title as t ON t.option_type_id = o.option_type_id WHERE p.product_id = " . $product_id;
		return $this->sqlread->fetchAll($query);
	}
	public function getContainerProductURL($entity_id, $manual_get = null) {
		//get parents entity
		$parent_id = $this->getContainerProductID($entity_id);

		//if parent exists
		if (!empty($parent_id)) {

			//reset parentid
			$parent_id = $parent_id[0]['product_id'];

			//write the url
			$url = $this->writeProductURL($parent_id, $manual_get);

		} else {
			$url = Mage::getBaseUrl() . Mage::getResourceModel('catalog/product')->getAttributeRawValue($entity_id, 'url_key', Mage::app()->getStore()->getStoreId());
		}

		return $url;
	}
	public function writeProductURL($product_id, $manual_get = null) {

		//compile url base
		$url = Mage::getBaseUrl() . Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, 'url_key', Mage::app()->getStore()->getStoreId());

		//compile get
		if (!empty($manual_get)) {
			//allows for appending values to the end of the url
			$url .= '.html?';
			//compile get
			if (is_array($manual_get)) {
				foreach ($manual_get as $key => $value) {
					$url .= $key . '=' . $value . '&';
				}
				$url = substr($url, 0, -1);
			} else {
				$url .= $manual_get;
			}
		}

		return $url;
	}

	public function getChildEntityIDFromCart($option_type_id) {
		$query = "SELECT DISTINCT e.entity_id FROM catalog_product_option AS p INNER JOIN catalog_product_option_type_value AS o ON o.option_id = p.option_id INNER JOIN catalog_product_entity AS e ON e.sku = o.sku WHERE option_type_id = " . $option_type_id;
		return $this->sqlread->fetchAll($query);
	}
	public function getContainerProductURLFromCart($product_id, $manual_get = null) {
		//compile url base
		$url = Mage::getBaseUrl() . Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, 'url_key', Mage::app()->getStore()->getStoreId());

		//compile get
		if (!empty($manual_get)) {
			//allows for appending values to the end of the url
			$url .= '.html?';
			//compile get
			if (is_array($manual_get)) {
				foreach ($manual_get as $key => $value) {
					$url .= $key . '=' . $value . '&';
				}
				$url = substr($url, 0, -1);
			} else {
				$url .= $manual_get;
			}
		}
		return $url;
	}
	public function _getImages($product_id, $include_default = false) {
		$query = "SELECT `id`,`product_id`,`manu`,`file_name`,`order`, `type`, `title`, `in_gallery`, `default_img` FROM `nps_product_media_gallery` WHERE `product_id` = " . $product_id;
		$query .= " ORDER BY `order`";
		$this->sqlread->query($query);
		$results = $this->sqlread->fetchAll($query);
		if (!$results) {
			if ($include_default) {
				$results = array(
					'manu' => 'needplumbingsupplies',
					'file_name' => 'noimagenps.jpeg',
					'title' => 'We\'re currently havign trouble locating this image',
				);
			} else {
				$results = array();
			}

		}
		return $results;
	}
}

?>
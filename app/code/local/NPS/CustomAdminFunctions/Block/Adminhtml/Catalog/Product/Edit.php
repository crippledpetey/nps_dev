<?php

class NPS_CustomAdminFunctions_Block_Adminhtml_Catalog_Product_Edit extends Mage_Adminhtml_Block_Catalog_Product_Edit {
	private $_product;

	protected function _prepareLayout() {
		//set product
		$this->_product = $this->getProduct();

		//run attribute update if triggered
		if (isset($_GET['process-child-attrs']) && $_GET['process-child-attrs'] == 'true') {
			$this->updateContainerProductAttributes($this->_product);

			//rewrite url for redirect
			session_write_close();
			Mage::app()->getFrontController()->getResponse()->setRedirect(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']));
		}

		//prepare the layout
		parent::_prepareLayout();

		//check if editing or not
		if (!is_null($this->getProduct()->getId())) {

			//set the object scope frontend product url
			$this->prdURL = Mage::getModel('core/url')->getUrl() . $this->_product->getUrlPath();

			// add the view on page button
			$this->setChild('view_on_front',
				$this->getLayout()->createBlock('adminhtml/widget_button')->setData(
					array(
						'label' => Mage::helper('catalog')->__('View Product Page'),
						'onclick' => 'window.open(\'' . $this->prdURL . $this->_getFirstChildLink() . '\')',
						'disabled' => !$this->_isVisible(),
						'title' => (!$this->_isVisible()) ?
						Mage::helper('catalog')->__('Product is not visible on frontend') :
						Mage::helper('catalog')->__('View Product Page'),
					)
				)
			);

			// add the re-link specs button (enabled if there are children)
			$this->setChild('process_child_attributes',
				$this->getLayout()->createBlock('adminhtml/widget_button')->setData(
					array(
						'label' => Mage::helper('catalog')->__('Process Childs Attributes'),
						'onclick' => 'window.location.replace(\'' . $_SERVER['REQUEST_URI'] . '?process-child-attrs=true\')',
						'title' => Mage::helper('catalog')->__('Process all child attributes values that are tagged to carry over'),
						'disabled' => !$this->_checkIfChildren(),
					)
				)
			);
		}

		return $this;
	}
	public function getViewOnPageButtonHtml() {
		return $this->getChildHtml('view_on_front');
	}
	public function getProcessAttributeButtonHtml() {
		return $this->getChildHtml('process_child_attributes');
	}
	private function _isVisible() {
		return $this->_product->isVisibleInCatalog() && $this->_product->isVisibleInSiteVisibility();
	}
	private function _checkIfChildren() {
		//start product drop helper
		require_once Mage::getBaseDir('base') . '/app/code/local/NPS/BetterLayerNavigation/Helper/product.drop.class.php';
		$nps_prdctrl = new productDrop;
		$test = $nps_prdctrl->getUrlOptionsForProduct($this->_product->getId());
		if (!empty($test)) {
			return true;
		} else {
			return false;
		}
	}
	private function _getFirstChildLink() {
		//start product drop helper
		require_once Mage::getBaseDir('base') . '/app/code/local/NPS/BetterLayerNavigation/Helper/product.drop.class.php';
		$nps_prdctrl = new productDrop;
		$urls = $nps_prdctrl->getUrlOptionsForProduct($this->_product->getId());
		if (!empty($urls)) {
			return '?npsf=' . $urls[0]['npsf'] . '&chid=' . $urls[0]['chid'];
		} else {
			return null;
		}
	}
	private function getChildrenProducts($product_id) {
		$query = "SELECT DISTINCT e.entity_id FROM catalog_product_option AS p INNER JOIN catalog_product_option_type_value AS o ON o.option_id = p.option_id INNER JOIN catalog_product_entity AS e ON e.sku = o.sku WHERE p.product_id = " . $product_id;
		return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
	}
	protected function updateContainerProductAttributes($product) {
		//require_once Mage::getBaseDir('base') . '/app/code/local/NPS/BetterLayerNavigation/Helper/product.drop.class.php';
		//$nps_prdctrl = new productDrop;

		//get products and child IDs
		$children = $this->getChildrenProducts($product->getId());

		//set connection
		$connection_write = Mage::getSingleton('core/resource')->getConnection('core_read');

		//blank array for updates
		$updates = array();

		//get attributes to carry over
		$select = $connection_write->select()->from('nps_attribute_options', array('id', 'attribute_id', 'options', 'parent_show', 'desc_show'))->where('parent_show=?', true);
		$attributes = $connection_write->fetchAll($select);

		//loop through attributes
		foreach ($attributes as $attr) {
			//set control data
			$data = json_decode($attr['options']);
			$child_temp = array();

			//check to make sure data is present
			if (!empty($data)) {
				//loop through children to get attr values
				foreach ($children as $child) {
					if ($value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($child['entity_id'], $data->attribute_id, Mage::app()->getStore()->getStoreId())) {
						//verify data is not empty
						if (!empty($value) && $value !== '' && $value !== ' ') {
							if ($data->attr_option_duplicate_handling == 'override') {
								$updates[$data->attribute_id] = $value;
							} elseif ($data->attr_option_duplicate_handling == 'append') {
								if (empty($updates[$data->attribute_id])) {$updates[$data->attribute_id] = '';}

								//remove duplicate options
								$combine = $updates[$data->attribute_id] . ',' . $value;
								$optionCheck = explode(',', str_replace(',,', ',', $combine));
								$optionCheck = array_unique($optionCheck);
								$newOptions = implode(",", $optionCheck);
								//set update
								$updates[$data->attribute_id] = ltrim($newOptions, ',');

							} elseif ($data->attr_option_duplicate_handling == 'hide') {
								if (!empty($updates[$data->attribute_id]) && $updates[$data->attribute_id] !== $value) {unset($updates[$data->attribute_id]);}
							} elseif ($data->attr_option_duplicate_handling == 'popular') {
								$updates[$data->attribute_id] = $value;
							} else {
								$updates[$data->attribute_id] = $value;
							}
						}
					}
				}
			}
		}

		//loop through updates
		foreach ($updates as $attr_code => $attr_val) {
			$newAttr = $product->setData($attr_code, $attr_val);
			if ($newAttr) {
				$newAttr->save();
			}
		}
	}
}
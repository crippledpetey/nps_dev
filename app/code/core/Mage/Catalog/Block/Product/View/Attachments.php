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
 * Product description block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_View_Attachments extends Mage_Core_Block_Template {
	protected $_product = null;

	function getProduct() {
		if (!$this->_product) {
			$this->_product = Mage::registry('product');
		}
		return $this->_product;
	}

	/**
	 * $excludeAttr is optional array of attribute codes to
	 * exclude them from additional data array
	 *
	 * @param array $excludeAttr
	 * @return array
	 */
	public function getAdditionalData(array $excludeAttr = array()) {
		$data = array();
		$product = $this->getProduct();
		$attributes = $product->getAttributes();
		foreach ($attributes as $attribute) {
//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
			if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
				$value = $attribute->getFrontend()->getValue($product);

				if (!$product->hasData($attribute->getAttributeCode())) {
					$value = Mage::helper('catalog')->__('N/A');
				} elseif ((string) $value == '') {
					$value = Mage::helper('catalog')->__('No');
				} elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
					$value = Mage::app()->getStore()->convertPrice($value, true);
				}

				if (is_string($value) && strlen($value)) {
					$data[$attribute->getAttributeCode()] = array(
						'label' => $attribute->getStoreLabel(),
						'value' => $value,
						'code' => $attribute->getAttributeCode(),
					);
				}
			}
		}
		return $data;
	}

	public function getAttachmentData($type = false) {
		$process_data = array();
		$return_data = false;

		//set type values
		$type_map = array(
			'product_attachment_spec' => array(
				'attr_id' => 1020,
				'title' => 'Specification Sheet',
				'domain' => '//images.needplumbingsupplies.com/',
				'folder' => 'spec_sheet',
				'allow_multiple' => true,
				'show_if_null' => false,
				'sin_message' => 'This product does not currently have any specification sheets',
			),
			'product_attachment_install' => array(
				'attr_id' => 1019,
				'title' => 'Installation Sheet',
				'domain' => '//images.needplumbingsupplies.com/',
				'folder' => 'install_sheet',
				'allow_multiple' => true,
				'show_if_null' => false,
				'sin_message' => 'This product does not currently have any installation sheets',
			),
			'parts_sheet' => array(
				'attr_id' => 1599,
				'title' => 'Parts Sheet / Diagram',
				'domain' => '//images.needplumbingsupplies.com/',
				'folder' => 'parts_sheet',
				'allow_multiple' => true,
				'show_if_null' => false,
				'sin_message' => 'This product does not currently have any part sheets or diagrams',
			),
			'owners_manual' => array(
				'attr_id' => 1600,
				'title' => 'Owners / Users Manual',
				'domain' => '//images.needplumbingsupplies.com/',
				'folder' => 'user_manual',
				'allow_multiple' => true,
				'show_if_null' => false,
				'sin_message' => 'This product does not currently have any manuals',
			),
		);
		//check for valid type
		if (!$type) {
			$process_data = $type_map;
		} else {
			//check if submitted type is a string or array
			if (is_array($type)) {
				//check each allowable type
				foreach ($type_map as $t => $vals) {
					//check if type was requested
					if (!empty($type[$t])) {
						//add to process data array
						$process_data[$t] = array();

						//set process data value
						foreach ($vals as $key => $default) {
							if (!isset($type[$t][$key])) {
								$process_data[$t][$key] = $type[$t][$key];
							} else {
								$process_data[$t][$key] = $default;
							}
						}
					}
				}
			} else {
//process single request
				//verify it matches allowable value
				if (!empty($type_map[$type])) {
					$process_data[$type] = $type_map[$type];
				}
			}
		}
		//set product
		$product = $this->getProduct();
		//manufacturer name and sku
		$manufacturer = strtolower(str_replace(' ', '_', $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product)));
		$sku = strtolower(str_replace(' ', '_', $product->getResource()->getAttribute('sku')->getFrontend()->getValue($product)));

		//loop through requested sheets
		foreach ($process_data as $attribute_code => $settings) {

			//get the asset file name
			$asset_file_value = $product->getResource()->getAttribute($attribute_code)->getFrontend()->getValue($product);

			//chweck if multiple is allowed
			if ($settings['allow_multiple']) {
				$asset_file_names = explode(',', $asset_file_value);
			} else {
				$asset_file_names[] = $asset_file_value;
			}
			if (!empty($asset_file_names)) {
				//set http / https
				$protocol = 'http:';
				if (!empty($_SERVER['HTTPS'])) {
					$protocol = 'https:';
				}

				foreach ($asset_file_names as $asset_file_name) {
					if (!empty($asset_file_name)) {
						//make sure .pdf is on the end
						$file_check = strtolower(substr($asset_file_name, -3));
						if ($file_check !== 'pdf') {
							$asset_file_name .= '.pdf';
						}

						//set file location variable
						$file_location = $protocol . $settings['domain'] . $settings['folder'] . '/' . $manufacturer . '/' . $asset_file_name;

						//check if file exists
						if ($this->remoteFileExists($file_location)) {
							$return_data[$attribute_code]['src'] = $file_location;
							$return_data[$attribute_code]['html'] = '<span class="attachment-icon">';

							$return_data[$attribute_code]['html'] .= '<a href="' . $file_location . '" title="Download the ' . $settings['title'] . ' for the ' . ucwords(str_replace('_', ' ', $manufacturer)) . ' ' . strtoupper($sku) . '" download>';
							$return_data[$attribute_code]['html'] .= '<img src="/media/css/' . $attribute_code . '_icon.png" alt="Download the ' . $settings['title'] . ' for the ' . ucwords(str_replace('_', ' ', $manufacturer)) . ' ' . strtoupper($sku) . '">';
							$return_data[$attribute_code]['html'] .= '</a>';
							$return_data[$attribute_code]['html'] .= '</span>';

							$return_data[$attribute_code]['html'] .= '<span class="attachment-link">';
							$return_data[$attribute_code]['html'] .= '<a class="tooltip" href="' . $file_location . '" title="" download>';
							$return_data[$attribute_code]['html'] .= ucwords(str_replace('_', ' ', $manufacturer)) . ' ' . strtoupper($sku) . ' - ' . $settings['title'];
							$return_data[$attribute_code]['html'] .= '</a>';
							$return_data[$attribute_code]['html'] .= '</span>';

						} elseif ($settings['show_if_null']) {
							//return null message if file is not present
							$return_data[$attribute_code]['html'] = '<p>' . $settings['sin_message'] . '</p>';
						}
					}
				}
			}
		}

		return $return_data;
	}

	public function remoteFileExists($url) {
		$curl = curl_init($url);

		//don't fetch the actual page, you only want to check the connection is ok
		curl_setopt($curl, CURLOPT_NOBODY, true);

		//do request
		$result = curl_exec($curl);

		$ret = false;

		//if request did not fail
		if ($result !== false) {
			//if request was ok, check response code
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if ($statusCode == 200) {
				$ret = true;
			}
		}

		curl_close($curl);

		return $ret;
	}
}

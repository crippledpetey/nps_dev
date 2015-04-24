<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/SmashingMagazine/
 *                     LogProductUpdate/Model/Observer.php
 */
class NPS_CustomAdminFunctions_Model_Observer {
	/**
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */

	public function __construct() {

	}

	public function updateProductTypeConfig(Varien_Event_Observer $observer) {

		$product = $observer->getEvent()->getProduct();

		$sql = 'UPDATE `catalog_product_entity_varchar` SET `value` = ? WHERE `entity_id` = ? AND `attribute_id` = ?';
		$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$connection_write->query($sql, array($product->getTypeId(), $product->getId(), 1598));

		//check if product is container type and update attributes if so
		$attributeSet = Mage::getModel("eav/entity_attribute_set")->load($product->getAttributeSetId())->_data['attribute_set_name'];

		//set the HipChat Message
		$message = ' has updated the ' . $product->getAttributeText('manufacturer') . ' ' . $product->getSku() . ' (id# ' . $product->getID() . ')';
		//output with admin name
		hipChatMsg('Merchandising', $message, true);

		//check if type is grouped
		if ($product->getAttributeSetId() == 93) {
			//$this->updateContainerProductAttributes($observer);
		}
	}

	public function updateGroupedProductPricing(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		$productType = $product->getTypeID();

		//check if type is grouped
		if ($productType == 'grouped') {
			$price = 0;

			//get associated products
			$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);

			//add price
			foreach ($associatedProducts as $ass_prd) {
				$price += number_format($ass_prd->getPrice(), 2);
			}

			$sql = 'UPDATE `catalog_product_entity_decimal` SET `value` = ? WHERE `entity_id` = ? AND `attribute_id` = ?';
			$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection_write->query($sql, array($price, $product->getId(), 99));

		}
	}

	public function updateGroupedProductInventory(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		$productType = $product->getTypeID();

		//check if type is grouped
		if ($productType == 'grouped') {
			$inv_total = 0;

			//get associated products
			$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);

			//add price
			foreach ($associatedProducts as $ass_prd) {
				$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($ass_prd);
				if ($stock->getQty() < $inv_total) {
					$inv_total = $stock->getQty();
				}
			}

			//check if over 0
			$in_stock = 0;
			if ($inv_total > 0) {$in_stock = 1;}

			$sql = 'UPDATE `cataloginventory_stock_item` SET `qty` = ?, is_in_stock = ? WHERE `product_id` = ?';
			$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$connection_write->query($sql, array($inv_total, $inv_total, $product->getId()));

		}
	}
	public function notifyHipChatEdit(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		//set the HipChat Message
		$message = ' opened the ' . $product->getAttributeText('manufacturer') . ' ' . $product->getSku() . ' for editing (id# ' . $product->getID() . ')';
		//output with admin name
		hipChatMsg('Merchandising', $message, true);
	}
	private function getChildrenProducts($product_id) {
		$query = "SELECT DISTINCT e.entity_id FROM catalog_product_option AS p INNER JOIN catalog_product_option_type_value AS o ON o.option_id = p.option_id INNER JOIN catalog_product_entity AS e ON e.sku = o.sku WHERE p.product_id = " . $product_id;
		return Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
	}
	static public function slugify($text) {
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

		// trim
		$text = trim($text, '-');

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// lowercase
		$text = strtolower($text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}

	public function updateContainerProductAttributes(Varien_Event_Observer $observer) {
		//get products and child IDs
		$product = $observer->getEvent()->getProduct();
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
						if ($data->attr_option_duplicate_handling == 'override') {
							$updates[$data->attribute_id] = $value;
						} elseif ($data->attr_option_duplicate_handling == 'append') {
							if (empty($updates[$data->attribute_id])) {$updates[$data->attribute_id] = '';}
							if (!stripos($updates[$data->attribute_id], $value)) {$updates[$data->attribute_id] .= ',' . $value;}
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

		//loop through updates
		foreach ($updates as $attr_code => $attr_val) {
			$newAttr = $product->setData($attr_code, $attr_val);
			if ($newAttr) {
				$newAttr->save();
			}
		}
	}

	public function updateUrlReWrite(Varien_Event_Observer $observer) {

		//check if new
		$is_new = $observer->getEvent()->getProduct()->isObjectNew();
		if ($is_new == false) {
			require_once Mage::getBaseDir('base') . '/app/code/local/NPS/BetterLayerNavigation/Helper/product.drop.class.php';

			//check fo make sure product is container product
			$containerPrdCheck = Mage::getModel("eav/entity_attribute_set")->load($observer->getEvent()->getProduct()->getAttributeSetId())->getData();
			if ($containerPrdCheck['attribute_set_name'] == 'Container Product') {

				//check to make sure required attributes are present
				$prdAttr = Mage::getModel('catalog/product')->load($observer->getEvent()->getProduct()->getId());

				//set variable that will be used
				$attr_manufacturer = $prdAttr->getAttributeText('manufacturer');
				$attr_container_productid = $prdAttr->getResource()->getAttribute('container_productid')->getFrontend()->getValue($prdAttr);
				$attr_url_key = $prdAttr->getResource()->getAttribute('url_key')->getFrontend()->getValue($prdAttr);

				//make sure required variables are present
				if (!empty($attr_container_productid) && !empty($attr_manufacturer)) {

					//get url ID
					$coreUrl = Mage::getModel('core/url_rewrite')->setStoreId(1)->loadByRequestPath($prdAttr->getUrlPath()); //
					$rwID = $coreUrl->getData()['url_rewrite_id'];

					//get existing rewrites
					$db_rewrites = $this->getRewrites($prdAttr->getId());

					//set static values for DB insertion
					$store_id = '1';
					$category_id = null;
					$product_id = null;
					$id_path = 'product/' . $prdAttr->getId();
					$target_path_base = $attr_url_key;
					$is_system = '0';
					$options = 'RP';
					$description = null;

					//compile new urls
					$rules = array();
					$url_manufacturer = self::slugify($attr_manufacturer);
					$url_container_productid = self::slugify($attr_container_productid);

					//start product drop class for obtaining custom option information
					$nps_options = new productDrop;
					if ($nps_options->getUrlOptionsForProduct($prdAttr->getId())) {

						//create array of rewrite URLS
						foreach ($nps_options->getUrlOptionsForProduct($prdAttr->getId()) as $key => $val) {

							//slugify finish
							$url_finish = self::slugify($val['title']);

							//set container product url
							$preferred = $url_manufacturer . '/' . $url_container_productid . '/' . $url_finish;
							$cp_target = $target_path_base . '.html?npsf=' . $val['npsf'] . '&chid=' . $val['chid'];

							//create redirects
							//$rules[] = 'Redirect 301 /product/' . $manufacturer . '/' . $url_container_productid . '/' . $url_finish . ' ' . $preferred;

							//create core rewrite
							$rules[] = $preferred . ' ' . $cp_target;
							$rules[] = $preferred . '/ ' . $cp_target;

							//$urls[] = 'product/' . $url_manufacturer . '/' . $url_container_productid . '/' . $url_finish_title . '?npsf=' . $val['npsf'] . '&chid=' . $val['chid']);
						}
					}

					if (!empty($rules)) {
						//get/create product rewrites files
						$prd_rw_file_path = Mage::getBaseDir('base') . DIRECTORY_SEPARATOR . 'rewrite' . DIRECTORY_SEPARATOR . 'rewritemap.txt';

						//find string
						$current = file_get_contents($prd_rw_file_path);
						$search_string = "#### " . $prdAttr->getId() . " ####"; //check for existing product info

						//if the product already has records
						if (stripos($current, $search_string)) {

							//explode the string to isolate the product entries
							$file_array = explode($search_string, $current);

							//start of file content
							$new_string = array($file_array[0]);

							//replace old rules in file
							$new_string[] = "#### " . $prdAttr->getId() . " ####\n";
							foreach ($rules as $rule) {
								$new_string[] = $rule . "\n";
							}
							$new_string[] = "#### " . $prdAttr->getId() . " ####";

							//re-append the end of the file
							$new_string[] = $file_array[2];

							//recompile into a string
							$new_string = implode(null, $new_string);

						} else {

							//kill the end line
							$new_string = str_replace("##\n# END REWRITE MAP FILE\n##", null, $current);

							//write new rules to end of file
							$new_string .= "#### " . $prdAttr->getId() . " ####\n";
							foreach ($rules as $rule) {
								$new_string .= $rule . "\n";
							}
							$new_string .= "#### " . $prdAttr->getId() . " ####\n";

							//readd file ending
							$new_string .= "\n##\n# END REWRITE MAP FILE\n##";
						}

						file_put_contents($prd_rw_file_path, $new_string);
					}
				}
			}
		}
	}

	protected function getRewrites($productID) {
		//start database connection and get rewrites from the DB
		$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$select = $connection_read->select()->from('core_url_rewrite', array('url_rewrite_id', 'store_id', 'category_id', 'product_id', 'id_path', 'request_path', 'target_path', 'is_system', 'options', 'description'))->where('`is_system` = 0 AND product_id=?', $productID);
		$rewrites = $connection_read->fetchAll($select);

		return $rewrites;
	}

	public function orderVendorProcessing($observer) {

		//check for po number and selected products
		if (isset($_POST['nps_source_vendor_po_number']) && !empty($_POST['vendor_source_product_id'])) {

			//start db connection
			$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');

			//convert observer data from json & base 64
			$obs_data = $_POST['nps_source_vendor_observer_info'];
			$obs_data = base64_decode($obs_data);
			$obs_data = json_decode($obs_data, true);

			//set order table variables
			$table_data['order_id'] = $obs_data['order_id'];
			$table_data['po_number'] = $_POST['nps_source_vendor_po_number'];
			$table_data['purchase_date'] = $obs_data['order_date'];
			$table_data['courier_code'] = $obs_data['vendors'][$_POST['nps_source_vendor_id']]['shipping_method']['po_code'];
			$table_data['shipping_name'] = $obs_data['shipping_receiving_name'];
			$table_data['address1'] = $obs_data['address1'];
			$table_data['address2'] = $obs_data['address2'];
			$table_data['city'] = $obs_data['city'];
			$table_data['region'] = $obs_data['region'];
			$table_data['postal_code'] = $obs_data['postal'];
			$table_data['country'] = $obs_data['country'];
			$table_data['phone'] = $obs_data['phone'];
			$table_data['buyer_email'] = $obs_data['buyer_email'];
			$table_data['buyer_name'] = $obs_data['buyer_name'];

			//ignored fields
			// $table_data['last_modified'] = 'CURRENT_TIMESTAMP';
			// $table_data['imported'] = 0;
			// $table_data['tracking_number'] = null;
			// $table_data['shipped'] = 0;

			//set product table variables
			$prd_table_data = array();

			//loop through available products
			foreach ($obs_data['vendors'][$_POST['nps_source_vendor_id']]['avail_prds'] as $op) {

				//verify item was selected before adding to array
				if (in_array($op['product_id'], $_POST['vendor_source_product_id'])) {
					//get order item information
					$query = "SELECT `sku`, `name`, `qty_ordered`, `base_price`, `price_incl_tax`, `base_row_total`, `row_total_incl_tax`, `weight`, `row_weight` FROM `sales_flat_order_item` WHERE `item_id` = " . $op['order_item_id'];
					$results = $connection_read->query($query);
					$prd_info = $connection_read->fetchRow($query);

					//set the array variables
					$prd_table_data[$op['product_id']]['order_id'] = $_POST['nps_source_vendor_order_id'];
					$prd_table_data[$op['product_id']]['po_number'] = $_POST['nps_source_vendor_po_number'];
					$prd_table_data[$op['product_id']]['tdp_uid'] = $op['vendor_uid'];
					$prd_table_data[$op['product_id']]['nps_uid'] = $op['product_id'];
					$prd_table_data[$op['product_id']]['sku'] = $prd_info['sku'];
					$prd_table_data[$op['product_id']]['name'] = $prd_info['name'];
					$prd_table_data[$op['product_id']]['qty_ordered'] = $prd_info['qty_ordered'];
					$prd_table_data[$op['product_id']]['unit_price'] = $prd_info['base_price'];
					$prd_table_data[$op['product_id']]['unit_price_incl_tax'] = $prd_info['price_incl_tax'];
					$prd_table_data[$op['product_id']]['line_price'] = $prd_info['base_row_total'];
					$prd_table_data[$op['product_id']]['line_price_incl_tax'] = $prd_info['row_total_incl_tax'];
					$prd_table_data[$op['product_id']]['unit_weight'] = $prd_info['weight'];
					$prd_table_data[$op['product_id']]['line_weight'] = $prd_info['row_weight'];
				}
			}

			//if there are products selected run the insert
			if (!empty($prd_table_data)) {
				//insert into the purchase order table
				$this->_addVendorPurchaseOrder($obs_data['vendors'][$_POST['nps_source_vendor_id']]['inv_table_values']['inv-po-table'], $table_data);
				//add order items to table
				$this->_addVendorPurchaseOrderItems($obs_data['vendors'][$_POST['nps_source_vendor_id']]['inv_table_values']['inv-po-item-table'], $prd_table_data);
			}

			//refresh page
			session_write_close();
			Mage::app()->getFrontController()->getResponse()->setRedirect($_SERVER['REQUEST_URI']);
		}
	}

	protected function _addVendorPurchaseOrder($table, $fields) {
		$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$cols = array();
		$vals = array();
		foreach ($fields as $name => $value) {
			$cols[] = "`" . $name . "`";
			if (empty($value)) {
				$vals[] = 'NULL';
			} else {
				$vals[] = "'" . $value . "'";
			}
		}
		$query = "INSERT INTO `" . $table . "` (" . implode(',', $cols) . ") VALUES (" . implode(",", $vals) . ")";
		$connection_write->query($query);
	}
	protected function _addVendorPurchaseOrderItems($table, $fields) {
		//start db connection
		$connection_write = Mage::getSingleton('core/resource')->getConnection('core_write');
		foreach ($fields as $prd_entity => $row) {

			//reset array of values
			$cols = array();
			$vals = array();

			//loop through rows
			foreach ($row as $name => $value) {
				$cols[] = "`" . $name . "`";
				if (empty($value)) {
					$vals[] = 'NULL';
				} else {
					$vals[] = "'" . $value . "'";
				}
			}
			//write value to database
			$query = "INSERT INTO `" . $table . "` (" . implode(',', $cols) . ") VALUES (" . implode(",", $vals) . ")";
			$connection_write->query($query);
		}
	}

}
<?php
/**
Error reporting
 */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$root_dir = str_replace('vendor-api-controllers', null, __DIR__);

/**
INCLUDE MAGENTO CORE FILES
 */
require_once $root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';

/**

START VENDOR CLASS TO CONTROL INTERACTIONS WITH THE DATABASE

 */
class vendorCorrespondence {
	// to view the code map see .codemap
	/**
	OBJECT PROPERTIES
	 */
	private $vendor_id;
	private $processingUpdateId;
	/**
	INFASTRUCTURE METHODS
	 */
	public function __construct($vendor_id) {
		Mage::app();
		//set sql connection
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');

		//set object scope props
		$this->vendor_id = $vendor_id;

		//object scope vendor Info
		$this->vendorInfo = $this->_getVendorInfo();

		//output updates
		$updates = $this->_getVendorUpdates('`processed` = false');
		echo "THERE ARE CURRENTLY " . count($updates) . " UNPROCESSED UPDATES FOR " . $this->vendorInfo['vendor_label'] . "\n\n";

		//check for updates
		if (count($updates) > 0) {
			foreach ($updates as $key => $value) {
				//check the update code
				if ($value['code'] == 1) {
					//set the update table active ID so it can be updated after
					$this->processingUpdateId = $value['id'];
					//run update
					$this->orderStatusUpdate($value['entity_id'], $value['message']);
				}
			}
		}
	}
	/**
	SYSTEM INTERACTION METHODS
	 */
	private function orderStatusUpdate($order_id, $message) {
		echo 'UPDATING ORDER NUMBER ' . $order_id . "\n\n";
	}

	/**
	DATABASE METHODS
	 */
	private function _getVendorInfo() {
		$query = "SELECT `id`,`vendor_id`,`file_name`,`inv_uid_col`,`inv_qty_col`,`inv_col_count`,`vendor_label`,`po_table`,`po_item_table`,`po_table_field_map`,`po_item_table_field_map` FROM `nps_vendor` WHERE `id` = " . $this->vendor_id;
		$return = $this->sqlread->fetchRow($query);
		return $return;
	}
	private function _getVendorUpdates($where = null) {
		//app vendor ID limit
		if (empty($where)) {
			$where = 'WHERE `vendor_id` = ' . $this->vendor_id;
		} else {
			$where = ' WHERE ( `vendor_id` = ' . $this->vendor_id . ') AND (' . $where . ')';
		}
		$query = "SELECT `id`,`vendor_id`,`entity_id`,`code`,`command`,`created`,`updated`,`processed` FROM `nps_vendor_updates`" . $where;
		$return = $this->sqlread->fetchAll($query);
		return $return;
	}
	private function _setUpdateProcessed($update_id) {
		$query = "UPDATE `nps_vendor_updates` SET `updated` = CURRENT_TIMESTAMP, `processed` = 1 WHERE `id` = " . $update_id;
		$this->sqlwrite->query($query);
	}
}
//instantiate class if vendor id is selected
if (isset($argv[1])) {
	$vendor = new vendorCorrespondence($argv[1]);
}

?>
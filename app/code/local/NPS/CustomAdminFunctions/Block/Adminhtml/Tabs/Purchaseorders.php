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
 * NPS Media Manager
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class NPS_CustomAdminFunctions_Block_Adminhtml_Tabs_PurchaseOrders extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::_construct();
		$this->setTemplate('sales/order/view/tab/purchaseorder.phtml');

		//sql connector
		$this->resource = Mage::getSingleton('core/resource');
		$this->readConnection = $this->resource->getConnection('core_read');
		$this->writeConnection = $this->resource->getConnection('core_write');
	}
	public function getOrder() {
		return Mage::registry('current_order');
	}
	public function getTabLabel() {
		return Mage::helper('sales')->__('Purchase Orders');
	}
	public function getTabTitle() {
		return Mage::helper('sales')->__('Purchase Order Information');
	}
	public function canShowTab() {
		return true;
	}
	public function isHidden() {
		return false;
	}
	public function createEventsForObserver($data) {
		//Mage::dispatchEvent('nps_vendor_order_processor', $data);
	}
	public function _shipperTrackingLink($shipper, $tracking_number) {
		$return = array(
			'UPS' => 'http://wwwapps.ups.com/etracking/tracking.cgi?tracknum=' . $tracking_number,
			'FedEx' => 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber=' . $tracking_number . '&cntry_code=us',
			'USPS' => 'https://tools.usps.com/go/TrackConfirmAction.action?tRef=fullpage&tLc=1&text28777=&tLabels=' . $tracking_number,
		);
		if (!empty($return[$shipper])) {
			return $return[$shipper];
		} else {
			return 'Invalid Shipper';
		}

	}
	public function _getVendorShippingMethod($vendor_id, $po_code) {
		$query = "SELECT `id`,`vendor_id`, `mage_code`, `label`, `po_code`, `courier` FROM `nps_vendor_shipment_translator` WHERE `vendor_id` = " . $vendor_id . " AND `po_code` = '" . $po_code . "'";

		$this->readConnection->query($query);
		$results = $this->readConnection->fetchRow($query);
		return $results;
	}
	public function _getVendors($where = null) {

		//start base query
		$query = 'SELECT `id`, `vendor_id`, `file_name`, `inv_uid_col`, `inv_qty_col`, `inv_col_count`, `vendor_label`, `po_table`, `po_item_table` FROM `nps_vendor`';

		if (!empty($where)) {
			$query .= $where;
		}

		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}

	public function _getPurchaseOrders($table, $order_id) {
		$query = "SELECT * FROM `" . $table . "` WHERE `order_id` = " . $order_id;

		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}

	public function _getPurchaseOrderItems($table, $order_id, $po_number) {
		$query = "SELECT * FROM `" . $table . "` WHERE `order_id` = " . $order_id . " AND `po_number` = " . $po_number . " ORDER BY `po_number`, `id`";

		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}

}

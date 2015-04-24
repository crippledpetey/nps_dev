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

class NPS_CustomAdminFunctions_Block_Adminhtml_Tabs_Processorder extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::_construct();
		$this->setTemplate('sales/order/view/tab/process.phtml');

		//sql connector
		$this->resource = Mage::getSingleton('core/resource');
		$this->readConnection = $this->resource->getConnection('core_read');
		$this->writeConnection = $this->resource->getConnection('core_write');
	}
	public function getOrder() {
		return Mage::registry('current_order');
	}
	public function getTabLabel() {
		return Mage::helper('sales')->__('Vendor Processing');
	}
	public function getTabTitle() {
		return Mage::helper('sales')->__('Order Information');
	}
	public function canShowTab() {
		return true;
	}
	public function isHidden() {
		return false;
	}

	public function createEventsForObserver($data) {
		Mage::dispatchEvent('nps_vendor_order_processor', $data);
	}

	public function _getVendorShippingMethod($vendor_id, $shippingMethod) {
		$query = "SELECT `id`,`vendor_id`, `mage_code`, `label`, `po_code` FROM `nps_dev`.`nps_vendor_shipment_translator` WHERE `vendor_id` = " . $vendor_id . " AND `mage_code` = '" . $shippingMethod . "'";

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
	public function _getNextPurchaseOrder($order_id, $vendor_po_table) {
		$query = "SELECT * FROM `" . $vendor_po_table . "` WHERE `order_id` = " . $order_id;
		$result = $this->readConnection->fetchAll($query);
		$total_rows = count($result);

		if ($total_rows) {
			return str_pad($order_id, 7, '0', STR_PAD_LEFT) . '-' . $total_rows;
		} else {
			return str_pad($order_id, 7, '0', STR_PAD_LEFT) . '-0';
		}
	}
	public function _getItems($order_id) {
		$query = "SELECT `item_id`,`order_id`,`parent_item_id`,`quote_item_id`,`store_id`,`created_at`,`updated_at`,`product_id`,`product_type`,`product_options`,`weight`,`is_virtual`,`sku`,`name`,`description`,`applied_rule_ids`,`additional_data`,`free_shipping`,`is_qty_decimal`,`no_discount`,`qty_backordered`,`qty_canceled`,`qty_invoiced`,`qty_ordered`,`qty_refunded`,`qty_shipped`,`base_cost`,`price`,`base_price`,`original_price`,`base_original_price`,`tax_percent`,`tax_amount`,`base_tax_amount`,`tax_invoiced`,`base_tax_invoiced`,`discount_percent`,`discount_amount`,`base_discount_amount`,`discount_invoiced`,`base_discount_invoiced`,`amount_refunded`,`base_amount_refunded`,`row_total`,`base_row_total`,`row_invoiced`,`base_row_invoiced`,`row_weight`,`gift_message_id`,`gift_message_available`,`base_tax_before_discount`,`tax_before_discount`,`weee_tax_applied`,`weee_tax_applied_amount`,`weee_tax_applied_row_amount`,`base_weee_tax_applied_amount`,`base_weee_tax_applied_row_amnt`,`weee_tax_disposition`,`weee_tax_row_disposition`,`base_weee_tax_disposition`,`base_weee_tax_row_disposition`,`ext_order_item_id`,`locked_do_invoice`,`locked_do_ship`,`price_incl_tax`,`base_price_incl_tax`,`row_total_incl_tax`,`base_row_total_incl_tax`,`hidden_tax_amount`,`base_hidden_tax_amount`,`hidden_tax_invoiced`,`base_hidden_tax_invoiced`,`hidden_tax_refunded`,`base_hidden_tax_refunded`,`is_nominal`,`tax_canceled`,`hidden_tax_canceled`,`tax_refunded`,`base_tax_refunded`,`discount_refunded`,`base_discount_refunded` FROM `sales_flat_order_item` WHERE `order_id` = " . $order_id;

		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}

	public function _getVendorElligibleItems($order_id, $vendor_id, $vendor_id_num, $vendor_item_table) {
		$query = "SELECT `i`.`item_id`, `i`.`order_id`, `i`.`parent_item_id`, `i`.`quote_item_id`, `i`.`store_id`, `i`.`created_at`, `i`.`updated_at`, `i`.`product_id`, `i`.`product_type`, `i`.`product_options`, `i`.`weight`, `i`.`is_virtual`, `i`.`sku`, `i`.`name`, `i`.`description`, `i`.`applied_rule_ids`, `i`.`additional_data`, `i`.`free_shipping`, `i`.`is_qty_decimal`, `i`.`no_discount`, `i`.`qty_backordered`, `i`.`qty_canceled`, `i`.`qty_invoiced`, `i`.`qty_ordered`, `i`.`qty_refunded`, `i`.`qty_shipped`, `i`.`base_cost`, `i`.`price`, `i`.`base_price`, `i`.`original_price`, `i`.`base_original_price`, `i`.`tax_percent`, `i`.`tax_amount`, `i`.`base_tax_amount`, `i`.`tax_invoiced`, `i`.`base_tax_invoiced`, `i`.`discount_percent`, `i`.`discount_amount`, `i`.`base_discount_amount`, `i`.`discount_invoiced`, `i`.`base_discount_invoiced`, `i`.`amount_refunded`, `i`.`base_amount_refunded`, `i`.`row_total`, `i`.`base_row_total`, `i`.`row_invoiced`, `i`.`base_row_invoiced`, `i`.`row_weight`, `i`.`gift_message_id`, `i`.`gift_message_available`, `i`.`base_tax_before_discount`, `i`.`tax_before_discount`, `i`.`weee_tax_applied`, `i`.`weee_tax_applied_amount`, `i`.`weee_tax_applied_row_amount`, `i`.`base_weee_tax_applied_amount`, `i`.`base_weee_tax_applied_row_amnt`, `i`.`weee_tax_disposition`, `i`.`weee_tax_row_disposition`, `i`.`base_weee_tax_disposition`, `i`.`base_weee_tax_row_disposition`, `i`.`ext_order_item_id`, `i`.`locked_do_invoice`, `i`.`locked_do_ship`, `i`.`price_incl_tax`, `i`.`base_price_incl_tax`, `i`.`row_total_incl_tax`, `i`.`base_row_total_incl_tax`, `i`.`hidden_tax_amount`, `i`.`base_hidden_tax_amount`, `i`.`hidden_tax_invoiced`, `i`.`base_hidden_tax_invoiced`, `i`.`hidden_tax_refunded`, `i`.`base_hidden_tax_refunded`, `i`.`is_nominal`, `i`.`tax_canceled`, `i`.`hidden_tax_canceled`, `i`.`tax_refunded`, `i`.`base_tax_refunded`, `i`.`discount_refunded`, `i`.`base_discount_refunded`, `vendor_id`.`vendor_uid`, `vendor_id`.`qty_avail`, `vendor_id`.`expected_date`, `vendor_id`.`vendor_cost` as `vendor_cost` FROM `sales_flat_order_item` `i` INNER JOIN (SELECT DISTINCT `EA`.`attribute_code`, `EAV`.`value` AS `vendor_uid`, `EAV`.`entity_id` AS `product_id`, `inv`.`qty` AS `qty_avail`, `inv`.`eta` AS `expected_date`, `inv`.`cost` AS `vendor_cost` FROM `eav_attribute` AS `EA` LEFT JOIN `catalog_product_entity_varchar` AS `EAV` ON `EAV`.`attribute_id` = `EA`.`attribute_id` LEFT JOIN `nps_inventory_staging` AS `inv` ON `inv`.`vendor_uid` = `EAV`.`value` AND `inv`.`vendor_id` = " . $vendor_id_num . " WHERE `attribute_code` LIKE '%" . $vendor_id . "_uid%') AS `vendor_id` ON `i`.`product_id` = `vendor_id`.`product_id` AND `vendor_uid` IS NOT NULL LEFT OUTER JOIN `" . $vendor_item_table . "` `t` ON `i`.`product_id` = `t`.`nps_uid` WHERE `i`.`order_id` = " . $order_id . " AND `t`.`id` IS NULL ";

		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}
}
<?php

class NPS_CustomAdminFunctions_Block_Adminhtml_Ordertabs extends Mage_Adminhtml_Block_Sales_Order_View_Tabs {
	public $parent;

	protected function _prepareLayout() {

		//get all existing tabs
		$this->parent = parent::_prepareLayout();

		//Add supplemental order tabs
		$this->addTabAfter('order_vendor_process', array(
			'label' => Mage::helper('catalog')->__('Vendor Processing'),
			'content' => $this->getLayout()->createBlock('customadminfunctions/adminhtml_tabs_processorder')->toHtml(),
		), 'order_info');

		$this->addTabAfter('order_vendor_purchase_orders', array(
			'label' => Mage::helper('catalog')->__('Purchase Orders'),
			'content' => $this->getLayout()->createBlock('customadminfunctions/adminhtml_tabs_purchaseorders')->toHtml(),
		), 'order_transactions');

		return $this->parent;
	}
}
<?php
class NPS_PurchaseOrderManagement_NPSPurchaseOrderManagementController extends Mage_Adminhtml_Controller_Action {
	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
}
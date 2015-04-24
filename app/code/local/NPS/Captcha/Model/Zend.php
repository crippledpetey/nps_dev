<?php
class NPS_Captcha_Model_Zend extends Mage_Captcha_Model_Zend{

	protected function _isUserAuth(){
	/* return Mage::app()->getStore()->isAdmin()
	? Mage::getSingleton(‘admin/session’)->isLoggedIn()
	: Mage::getSingleton(‘customer/session’)->isLoggedIn();*
	*/
	}

}

?>
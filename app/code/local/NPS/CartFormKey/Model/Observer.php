<?php

class NPS_CartFormKey_Model_Observer {
	function disableCsrf(Varien_Event_Observer $observer) {
		$events = array(
			'checkout_cart_add',
			'checkout_cart_addgroup',
			'checkout_cart_updatepost',
			'review_product_post',
			'sendfriend_product_sendmail',
			'wishlist_index_add',
			'wishlist_index_update',
			'wishlist_index_cart',
			'wishlist_index_send',
			'catalog_product_compare_add',
		);
		$route = $observer->getEvent()->getControllerAction()->getFullActionName();
		outputToTestingText($route, true);

		if (in_array($route, $events) && !empty(Mage::getSingleton('core/session')->getFormKey()) && Mage::getSingleton('core/session')->getFormKey()) {
			$key = Mage::getSingleton('core/session')->getFormKey();
			Mage::app()->getRequest()->setParam('form_key', $key);
		}
	}
}
<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/SmashingMagazine/
 *                     LogProductUpdate/Model/Observer.php
 */
class NPS_BetterLayerNavigation_Model_Observer {
	/**
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */
	public function logUpdate(Varien_Event_Observer $observer) {
		// Retrieve the product being updated from the event observer
		$product = $observer->getEvent()->getProduct();

		// Write a new line to var/log/product-updates.log
		$name = $product->getName();
		$sku = $product->getSku();
		Mage::log(
			"{$name} ({$sku}) updated",
			null,
			'product-updates.log'
		);
	}

	protected function getChildIDFromParent() {
		$query = "SELECT o.option_type_id FROM catalog_product_option AS p INNER JOIN catalog_product_option_type_value AS o ON o.option_id = p.option_id INNER JOIN catalog_product_entity AS e ON e.sku = o.sku WHERE e.entity_id = " . $entity_id;
		return $this->sqlread->fetchAll($query);
	}

	public function updateProductCookiesForNPSF(Varien_Event_Observer $observer, $npsf = null) {

		//check for existing cookie and decode if necessary
		$cookie_id = base64_encode('nps_previous_products');

		//check for clear all
		if (isset($_GET['clearRecentProducts'])) {
			setcookie($cookie_id, 'REMOVE', -1000, '/');
		}
		//set the product that was clicked
		$product = $observer->getEvent()->getProduct();

		if (isset($_COOKIE[$cookie_id])) {
			$value_array = json_decode(base64_decode($_COOKIE[$cookie_id]), true);
		} else {
			$value_array = array();
		}

		//inverse the array
		$value_array = array_reverse($value_array);

		//set the flat values
		$manufacturer = $product->getAttributeText('manufacturer');
		$sku = $product->getSKU();
		$title = $product->getAttributeText('manufacturer') . ' ' . $product->getSKU() . ' - ' . $product->getName();

		//check product type
		$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
		$attributeSetModel->load($product->getAttributeSetId());
		$attributeSetName = $attributeSetModel->getAttributeSetName();

		//check to make sure it's a container product
		if ($attributeSetName == 'Container Product') {

			//set npsf
			$npsf = $_GET['npsf'];
			$npsf_url = 'npsf=' . $npsf;
			$npsf_cookie_append = '-' . $npsf;

			//set chid
			$chid = $_GET['chid'];
			$chid_url = 'chid=' . $chid;
			$chid_cookie_append = '-' . $chid;

			//set image
			$image_id = $chid;

			$child_product = Mage::getModel('catalog/product')->load($chid);
			$cart_link = Mage::helper('checkout/cart')->getAddUrl($child_product);

			$price = number_format($child_product->getPrice(), 2);

		} else {

			//set npsf
			$npsf = null;
			$npsf_url = null;
			$npsf_cookie_append = null;

			//set chid
			$chid = null;
			$chid_url = null;
			$chid_cookie_append = null;

			//set image
			$image_id = $product->getID();

			$cart_link = Mage::helper('checkout/cart')->getAddUrl($product);

			$price = number_format($product->getPrice(), 2);
		}

		//set the cookie array key PRODUCT ID+CHILD ID (if exists)
		$cookie_key = $product->getID() . $npsf_cookie_append;

		//check if existing value is present and remove if so
		if (!empty($value_array[$cookie_key])) {unset($value_array[$cookie_key]);}

		//set the child id to enable gathering of the image and link information
		$img_prd = Mage::getModel('catalog/product')->load($image_id);
		$img_path_url = $img_prd->getImage();

		//create value array
		$value_array[$cookie_key] = array(
			'parent_id' => $product->getID(),
			'npsf' => $npsf,
			'chid' => $chid,
			'img' => $img_path_url,
			'url' => $_SERVER['REQUEST_URI'],
			'title' => $title,
			'manufacturer' => $manufacturer,
			'sku' => $sku,
			'price' => $price,
			'cart_link' => $cart_link,
		);

		//inverse array
		$value_array = array_reverse($value_array);

		//set cookie values
		$cookieValue = base64_encode(json_encode($value_array));
		$cookieExpire = 0;
		$cookieDomain = '/';
		setcookie($cookie_id, $cookieValue, $cookieExpire, $cookieDomain);
	}

	public function updateFormKeyForCaching(Varien_Event_Observer $observer) {
		//set the product that was clicked
		//$product = $observer->getEvent()->getProduct();
		$sessionKey = Mage::getSingleton('core/session')->getFormKey();
		$observer->getEvent()->getRequest()->setParam('form_key', $sessionKey);
	}
}
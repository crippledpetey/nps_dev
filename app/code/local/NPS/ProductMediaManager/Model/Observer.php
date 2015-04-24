<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/SmashingMagazine/
 *                     LogProductUpdate/Model/Observer.php
 */
class NPS_ProductMediaManager_Model_Observer {
	/**
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */

	public function __construct() {

		//sql connector
		$this->resource = Mage::getSingleton('core/resource');
		$this->sqlread = $this->resource->getConnection('core_read');
		$this->sqlwrite = $this->resource->getConnection('core_write');

	}
	public function mageImageToMediaGallery(Varien_Event_Observer $observer) {
		/*
	$product = $observer->getEvent()->getProduct();
	$params = Mage::app()->getRequest()->getParams();
	if ($params['nps-mage-image-import-trigger'] == 'true') {
	$image = $params['product']['media_gallery'];
	$images = json_decode($image['images']);
	outputToTestingText(null);
	foreach ($images as $key => $value) {
	outputToTestingText('KEY:', true);
	outputToTestingText($key, true);
	outputToTestingText('VALUE:', true);
	outputToTestingText($value, true);
	}
	}*/
	}
	public function updateGalleryOrder(Varien_Event_Observer $observer) {

		$product = $observer->getEvent()->getProduct();
		$params = Mage::app()->getRequest()->getParams();

		if (isset($params['nps-gallery'])) {
			$galleryArray = $params['nps-gallery'];

			//check to see if any of the image orders have changed
			foreach ($galleryArray as $imgID => $ordr) {

				//check for title update
				if (!empty($ordr['finish-title']) && $ordr['finish-title-change'] == 'true') {
					$this->sqlwrite->query("UPDATE `nps_product_media_gallery` SET `title` = '" . $ordr['finish-title'] . "' WHERE `id` = " . $imgID);
				}

				//check for title update
				if (!empty($ordr['img-title']) && $ordr['img-title-change'] == 'true') {
					$this->sqlwrite->query("UPDATE `nps_product_media_gallery` SET `title` = '" . $ordr['img-title'] . "' WHERE `id` = " . $imgID);
				}

				//check for order change
				if (!empty($ordr['old-order']) && !empty($ordr['new-order'])) {
					$old = $ordr['old-order'];
					$new = $ordr['new-order'];

					//if there was an update
					if ($new !== $old) {
						$updateThisImage = "UPDATE `nps_product_media_gallery` SET `order` = " . $new . " WHERE `id` = " . $imgID;

						//going up or down?
						if ($new > $old) {
							//set query going up
							$updateOtherImages = "UPDATE `nps_product_media_gallery` SET `order` = `order` - 1 WHERE ( `product_id` = '" . $product->getId() . "' ) AND ( `order` BETWEEN " . $old . " AND " . $new . ") AND ( `id` <> " . $imgID . " ) ";
						} else {
							//set query going down
							$updateOtherImages = "UPDATE `nps_product_media_gallery` SET `order` = `order` + 1 WHERE ( `product_id` = '" . $product->getId() . "' ) AND ( `order` BETWEEN " . $new . " AND " . $old . ") AND ( `id` <> " . $imgID . " ) ";
						}
						//update the database
						$this->sqlwrite->query($updateThisImage);
						$this->sqlwrite->query($updateOtherImages);
					}
				}
			}
		}
	}
}
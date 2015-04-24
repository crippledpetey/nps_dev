<?php
class NPS_CategorySeo_Model_Observer {
	/**
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */

	public function __construct() {
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
	}

	public function categorySave(Varien_Event_Observer $observer) {
		//set the posted parameters
		$params = $observer->getRequest()->getParams();
		$seoPost = $params['general'];

		//check to see if the values are are
		if (!empty($seoPost['id'])) {
			//set the field names to check for col name => post field
			$seoFields = array(
				'category_id' => array('seo_category_id', 'text'),
				'category_type' => array('seo_category_type', 'select'),
				'is_primary' => array('seo_is_primary', 'checkbox'),
				'is_child' => array('seo_is_child', 'checkbox'),
				'parent_id' => array('seo_parent_id', 'text'),
				'redirect' => array('seo_redirect', 'checkbox'),
				'redirect_type' => array('seo_redirect_type', 'select'),
				'canonical' => array('seo_canonical', 'checkbox'),
				'gen_info' => array('seo_gen_info', 'checkbox'),
				'design_info' => array('seo_design_info', 'checkbox'),
				'display_info' => array('seo_display_info', 'checkbox'),
				'breadcrumb' => array('seo_breadcrumb', 'checkbox'),
			);

			//empty array to control update
			$updateFields = array();

			//loop through available fields to check for updates
			foreach ($seoFields as $db => $post) {
				//check posted field type
				if ($post[1] == 'checkbox') {
					//check if checkbox is checked
					if (isset($seoPost[$post[0]])) {
						$updateFields[$db] = '1';
					} else {
						$updateFields[$db] = '0';
					}
				} else {
					//if there is a value set add to array or remove it if not
					if (!empty($seoPost[$post[0]])) {
						$updateFields[$db] = $seoPost[$post[0]];
					} else {
						$updateFields[$db] = null;
					}
				}
			}

			//fill in extra redirect type boxes
			if ($seoPost['seo_category_type'] == 'canonical') {
				$updateFields['canonical'] = '1';
			} elseif ($seoPost['seo_category_type'] == 'redirect') {
				$updateFields['redirect'] = '1';
				//check to make sure redirect type is filled in
				if (empty($updateFields['seo_redirect_type'])) {
					$updateFields['redirect_type'] = '301';
				}
			}

			//check if update or insert
			$query = '';
			if ($seoPost['seo_is_new'] == 0) {
				$query = "UPDATE `catalog_category_seo` SET ";
				foreach ($updateFields as $field => $value) {
					$query .= " " . $field . " = '" . $value . "'";
				}
				$query .= " WHERE `category_id` = " . $params['id'];
			} else {
				$query = "INSERT INTO `catalog_category_seo` ";
				foreach ($updateFields as $field => $value) {
					$db_fields[] = "`" . $field . "`";

					//check if value is null
					if (empty($value) || $value == '' || is_null($value)) {
						$db_values[] = 'NULL';
					} else {
						$db_values[] = "'" . $value . "'";
					}

				}
				$compiledFields = implode(",", $db_fields);
				$compiledValues = implode(",", $db_values);
				$query .= "(" . $compiledFields . ") VALUES (" . $compiledValues . ")";
			}

			$this->sqlwrite->query($query);
		}
	}

}

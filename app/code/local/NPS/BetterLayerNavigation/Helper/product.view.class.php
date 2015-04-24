<?php
/**
CUSTOM DROP PHP FUNCTIONS
 */
class productView {

	public function __construct() {
		//database read adapter
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		//database table prefix
		$this->tablePrefix = (string) Mage::getConfig()->getTablePrefix();

		$this->storeID = Mage::app()->getStore()->getStoreId();
	}

	private function generateSubDescListHtml($_product, $display_attrs, $region) {
		$html = null;

		foreach ($display_attrs as $attr) {

			//set options
			$options = json_decode($attr['options'], true);
			if ($_product->getResource()->getAttribute($attr['attribute_code'])) {
				//check for value
				if ($value = $_product->getResource()->getAttribute($attr['attribute_code'])->getFrontend()->getValue($_product)) {
					//start list if necessary
					if (empty($html)) {
						$html = '<ul id="sub-desc-' . $region . '-list" class="prd-sub-desc-list">';
					}

					//check for supplemental information
					$value_supp = null;
					if (!empty($options['nps_attr_option_' . $region . '_list_supp'])) {
						$value_supp = '<span class="sub-desc-list-supp">(' . $options['nps_attr_option_' . $region . '_list_supp'] . ')</span>';
					}

					//check for uom
					$uom = null;
					if (!empty($options['attr_option_add_uom'])) {
						$uom = '<span class="sub-desc-list-uom">' . $options['attr_option_add_uom'] . '</span>';
					}

					$html .= '<li><span class="sub-desc-list-label">' . ucwords($attr['frontend_label']) . ':</span><span class="sub-desc-list-value">' . $value . $uom . '</span>' . $value_supp . '</li>';
				}
			}
		}
		if (!empty($html)) {$html .= '</ul>';}

		return $html;
	}
	private function generateSubDescTechListHtml($_product, $manufacturer, $display_attrs) {
		$html = null;
		//loop through attributes
		foreach ($display_attrs as $attr) {

			//check if product has data for the attribute
			$check_value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), $attr['attribute_code'], 0);
			if ($check_value) {
				//set options
				$options = json_decode($attr['options'], true);
				//check for value
				if ($options['nps_attr_option_tech_description']) {

					$html .= '<div class="manufacturer-tech-block">';
					$html .= '	<h3 class="manu-tech-list-label">' . ucwords($manufacturer) . ' ' . ucwords($attr['frontend_label']) . '</h3>';
					$html .= '	<div class="manu-tech-list-value">' . $options['nps_attr_option_tech_description'] . '</div>';
					$html .= '</div>';
				}
			}
		}
		if (!empty($html)) {$html .= '</ul>';}

		return $html;
	}

	public function getFeatures($_product, $_shortcode_class) {

		//get attributes
		$display_attrs = $this->getRelevantAttributes('feat');
		$attribute_supp = $this->generateSubDescListHtml($_product, $display_attrs, 'feat');

		#var_dump($display_attrs);

		$return = null;
		$value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), 'nps_desc_region_feat', $this->storeID);
		if (!empty($value) || !empty($attribute_supp)) {
			$return = '<div class="product-collateral product-subdescription"><div class="box-collateral box-description" style="width: 100%;"><h2>Features</h2><div class="product-subdescription-autopop">' . $attribute_supp . '</div><div class="std">' . $value . '</div></div><div class="clearer"></div></div>';
		}
		$return = $this->processShortcodes($_shortcode_class, $return);
		return $return;
	}

	public function getSpecs($_product, $_shortcode_class) {
		//get attributes
		$display_attrs = $this->getRelevantAttributes('specs');
		$attribute_supp = $this->generateSubDescListHtml($_product, $display_attrs, 'specs');

		$return = null;
		$value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), 'nps_desc_region_specs', $this->storeID);
		if (!empty($value) || !empty($attribute_supp)) {
			$return = '<div class="product-collateral product-subdescription"><div class="box-collateral box-description" style="width: 100%;"><h2>Key Specifications</h2><div class="product-subdescription-autopop">' . $attribute_supp . '</div><div class="std">' . $value . '</div></div><div class="clearer"></div></div>';
		}
		$return = $this->processShortcodes($_shortcode_class, $return);
		return $return;
	}

	public function getTech($_product, $_shortcode_class) {
		//set manufacturer name
		$manu = $_product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($_product);

		//get attributes
		$display_attrs = $this->getRelevantAttributes('tech');
		$attribute_supp = $this->generateSubDescTechListHtml($_product, $manu, $display_attrs);

		//begin output
		$return = null;
		$value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), 'nps_desc_region_tech', $this->storeID);
		if (!empty($value) || !empty($attribute_supp)) {

			//get the manufacturer
			$manu = $_product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($_product);

			$return = '<div class="product-collateral product-subdescription manufacturer-technologies">';
			$return .= '	<div class="box-collateral box-description" style="width: 100%;">';
			$return .= '		<h2>' . ucwords($manu) . ' Technologies</h2>';
			$return .= '		<div class="product-subdescription-autopop">' . $attribute_supp . '</div>';
			$return .= '		<div class="std">' . $value . '</div>';
			$return .= '	</div>';
			$return .= '	<div class="clearer"></div>';
			$return .= '</div>';
		}
		$return = $this->processShortcodes($_shortcode_class, $return);
		return $return;
	}

	public function getMaint($_product, $_shortcode_class) {
		//get attributes
		$display_attrs = $this->getRelevantAttributes('maint');
		$attribute_supp = $this->generateSubDescListHtml($_product, $display_attrs, 'maint');

		$return = null;
		$value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), 'nps_desc_region_maint', $this->storeID);
		if (!empty($value) || !empty($attribute_supp)) {
			//get the manufacturer
			$manu = $_product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($_product);
			//Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), 'manufacturer', $this->storeID);
			$return = '<div class="product-collateral product-subdescription"><div class="box-collateral box-description" style="width: 100%;"><h2>' . ucwords($manu) . ' Suggested Maintenance</h2><div class="product-subdescription-autopop">' . $attribute_supp . '</div><div class="std">' . $value . '</div></div><div class="clearer"></div></div>';
		}

		$return = $this->processShortcodes($_shortcode_class, $return);
		return $return;
	}
	protected function processShortcodes($_shortcode_object, $content) {
		//get array of description shortcode locations
		$shortcodes = $_shortcode_object->getShotcodeLocations($content);

		//if there are shortcodes process them
		if ($shortcodes) {
			//get the data
			$shortcodes = $_shortcode_object->getShortcodeData($shortcodes, $content);
			//reset the description with the new shortcodes
			$content = $_shortcode_object->processShortcodeData($shortcodes, $content);
		}

		//ouput description
		return $content;
	}
	public function getRelevantAttributes($region) {

		//check for existing option
		$select = $this->sqlwrite->select()->from('nps_prd_desc_region_' . $region, array('attribute_id', 'options', 'parent_show', 'desc_show', 'attribute_code', 'frontend_label', 'frontend_input'));
		$rowsArray = $this->sqlread->fetchAll($select);
		return $rowsArray;
	}
	//attributes that will nevere be displayed as specs or carried to container products
	public static function getBlackListedAttributes() {
		return array(97, 98, 100, 101, 103, 104, 105, 106, 109, 110, 270, 271, 272, 273, 274, 476, 481, 492, 493, 494, 495, 498, 503, 506, 507, 508, 509, 526, 531, 562, 567, 568, 569, 570, 571, 572, 573, 703, 704, 705, 836, 837, 838, 859, 860, 861, 862, 863, 873, 876, 879, 880, 881, 903, 904, 905, 906, 931, 932, 933, 935, 936, 941, 942, 943, 952, 960, 962, 994, 1019, 1020, 1106, 1158, 1575, 1251, 1463, 1433, 1566, 1567, 1568, 1489, 1583, 1585, 1586, 1587, 1588, 1589, 1590, 1591, 1592, 1593, 1594, 1595, 1596, 1597, 1598, 1599, 1600, 1601, 1602, 1603, 1604, 1605, 96, 99, 102, 940);
	}
}
?>
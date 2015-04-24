<?php
/* app/code/local/NPS/BetterLayerNavigation/Block/Catalog/Layer/Filter/Price.php */

class NPS_BetterLayerNavigation_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Abstract {

	public function __construct() {
		parent::__construct();
		$this->_filterModelName = 'nps_beeterlayernavigation/catalog_layer_filter_price';
	}

}

?>
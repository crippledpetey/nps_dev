<?php
/* app/code/local/NPS/BetterLayerNavigation/Model/Catalog/Layer/Filter/Price.php */

class NPS_BetterLayerNavigation_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Abstract {

	const FILTER_ON_SALE = 1;
	const FILTER_NOT_ON_SALE = 2;

	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->_requestVar = 'price';
	}

	/**
	 * Apply sale filter to layer
	 *
	 * @param   Zend_Controller_Request_Abstract $request
	 * @param   Mage_Core_Block_Abstract $filterBlock
	 * @return  Mage_Catalog_Model_Layer_Filter_Sale
	 */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
		$filter = (int) $request->getParam($this->getRequestVar());
		if (!$filter || Mage::registry('nps_price_filter')) {
			return $this;
		}

		$select = $this->getLayer()->getProductCollection()->getSelect();
		/* @var $select Zend_Db_Select */

		if ($filter == self::FILTER_ON_SALE) {
			$select->where('price_index.final_price < price_index.price');
			$stateLabel = Mage::helper('nps_price')->__('On Sale');
		} else {
			$select->where('price_index.final_price >= price_index.price');
			$stateLabel = Mage::helper('nps_price')->__('Not On Sale');
		}

		$state = $this->_createItem(
			$stateLabel, $filter
		)->setVar($this->_requestVar);
		/* @var $state Mage_Catalog_Model_Layer_Filter_Item */

		$this->getLayer()->getState()->addFilter($state);

		Mage::register('nps_price_filter', true);

		return $this;
	}

	/**
	 * Get filter name
	 *
	 * @return string
	 */
	public function getName() {
		return Mage::helper('nps_price')->__('Sale');
	}

	/**
	 * Get data array for building sale filter items
	 *
	 * @return array
	 */
	protected function _getItemsData() {
		$data = array();
		$status = $this->_getCount();

		$data[] = array(
			'label' => Mage::helper('nps_price')->__('On Sale'),
			'value' => self::FILTER_ON_SALE,
			'count' => $status['yes'],
		);

		$data[] = array(
			'label' => Mage::helper('nps_price')->__('Not On Sale'),
			'value' => self::FILTER_NOT_ON_SALE,
			'count' => $status['no'],
		);
		return $data;
	}

	protected function _getCount() {
		// Clone the select
		$select = clone $this->getLayer()->getProductCollection()->getSelect();
		/* @var $select Zend_Db_Select */

		$select->reset(Zend_Db_Select::ORDER);
		$select->reset(Zend_Db_Select::LIMIT_COUNT);
		$select->reset(Zend_Db_Select::LIMIT_OFFSET);
		$select->reset(Zend_Db_Select::WHERE);

		// Count the on sale and not on sale
		$sql = 'SELECT IF(final_price >= price, "no", "yes") as on_sale, COUNT(*) as count from ('
		. $select->__toString() . ') AS q GROUP BY on_sale';

		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		/* @var $connection Zend_Db_Adapter_Abstract */

		return $connection->fetchPairs($sql);
	}

}

?>
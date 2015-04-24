<?php

class NPS_CategorySeo_IndexController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

		//create connection
		$this->setConnection();

		//set page variables
		$this->setNPSClassVars();

		//run all pre head commands
		$this->requestFunctions();

		//set the primary display content
		$DS = DIRECTORY_SEPARATOR;
		//$primaryContent = '<style>' . file_get_contents(Mage::getBaseDir('base') . $DS . 'app' . $DS . 'code' . $DS . 'local' . $DS . 'NPS' . $DS . 'CategorySEO' . $DS . 'Helper' . $DS . 'CategorySEOStyle.css') . '</style>';
		$primaryContent .= '<div id="nps-category-seo-container">';
		//$primaryContent .= call_user_func(array($this, $displayModes[$this->btf]));
		$primaryContent .= '</div>';

		//load the layout
		$this->loadLayout();

		//render the layout
		$this->renderLayout();
	}
/**
PAGE LOAD FUNCTIONS THAT CONTROL UPDATES
 */
	private function setConnection() {
		//database read adapter
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		//database table prefix
		$this->tablePrefix = (string) Mage::getConfig()->getTablePrefix();
	}
	public function requestFunctions() {

		//set default refresh values
		$refresh = false;
		$append_url = null;

		//if refresh is true then reload the page to prevent duplicate posting
		if ($refresh) {
			session_write_close();
			if (!empty($append_url)) {$append_url = '?' . $append_url;}
			Mage::app()->getFrontController()->getResponse()->setRedirect($_SERVER['REQUEST_URI'] . $append_url);
		}
	}

/**
HTML OUTPUT MEHTODS
 */

/**
DATABASE AND OTHER UPDATE METHODS CALLED BY  $this->requestFunctions()
 */
	public function _getVendor($vendor_id) {

		//start base query
		$query = 'SELECT `id`, `vendor_id`, `file_name`, `inv_uid_col`, `inv_qty_col`, `inv_col_count`, `vendor_label`, `po_table`, `po_item_table`,`po_table_field_map`,`po_item_table_field_map` FROM `nps_vendor` WHERE `id` = ' . $vendor_id;

		//get the result
		$this->sqlread->query($query);
		$results = $this->sqlread->fetchRow($query);
		return $results;
	}

/**
INFASTRUCTURE METHODS
 */

	private function setNPSClassVars() {
	}
	public function checked($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' checked ';
			}
		} else {
			return false;
		}
	}
	public function selected($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' selected ';
			}
		} else {
			return false;
		}
	}
	public function active($value, $test, $noOutput = false) {
		if ($value == $test) {
			if ($noOutput) {
				return true;
			} else {
				return ' active ';
			}
		} else {
			return false;
		}
	}

}
?>
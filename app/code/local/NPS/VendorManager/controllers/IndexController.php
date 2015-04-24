<?php

class NPS_VendorManager_IndexController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

		//create connection
		$this->setConnection();

		//set page variables
		$this->setNPSClassVars();

		//run all pre head commands
		$this->requestFunctions();

		//check for bthom function controlling the page output for primary content
		$this->btf = 0; //default welcome page
		if (isset($_GET['btf'])) {
			$this->btf = $_GET['btf'];
		}

		//function array to control output of primary content
		$displayModes = array(
			'npsVendorWelcomePage',
			'manageVendor',
		);

		//set the primary display content
		$DS = DIRECTORY_SEPARATOR;
		$primaryContent = '<style>' . file_get_contents(Mage::getBaseDir('base') . $DS . 'app' . $DS . 'code' . $DS . 'local' . $DS . 'NPS' . $DS . 'VendorManager' . $DS . 'Helper' . $DS . 'vendorManagerStyle.css') . '</style>';
		$primaryContent .= '<div id="nps-custom-attr-manager-container">' . call_user_func(array($this, $displayModes[$this->btf])) . '</div>';

		//load the layout
		$this->loadLayout();

		//set the menu item active
		$this->_setActiveMenu('sales/nps_vendor_manager_menu');

		//set left block
		$leftBlock = $this->getLayout()
		                  ->createBlock('core/text')
		                  ->setText($this->leftColumnHtml());

		//compile the lyout
		$block = $this->getLayout()
		              ->createBlock('core/text', 'nps-vendor-manager-control-panel')
		              ->setText($primaryContent);

		//add content block to layout
		$this->_addLeft($leftBlock);
		$this->_addContent($block);

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

		//check for vendor value updates
		if (!empty($_POST['nps_function'])) {
			//get function
			$func = $_POST['nps_function'];
			$updateFields = array();
			if ($func == 'vendor_manager_core_settings' && $_POST['nps_value_updated'] == 'true') {
				//compile variables
				$updateFields['file_name'] = $_POST['nps_vendor_inv_file'];
				$updateFields['inv_uid_col'] = $_POST['nps_vendor_inv_uid_col'];
				$updateFields['inv_qty_col'] = $_POST['nps_vendor_inv_qty_col'];
				$updateFields['inv_col_count'] = $_POST['nps_vendor_inv_col_count'];
				$updateFields['vendor_label'] = $_POST['nps_vendor_label'];
				$updateFields['po_table'] = $_POST['nps_vendor_po_table'];
				$updateFields['po_item_table'] = $_POST['nps_vendor_po_item_table'];

				//encode po and items table fields
				if (!empty($_POST['nps_vendor_po_table_fields'])) {
					$po_fields = explode("\n", $_POST['nps_vendor_po_table_fields']);
					$po_fields = serialize($po_fields);
					$updateFields['po_table_field_map'] = $po_fields;
				}
				if (!empty($_POST['nps_vendor_po_item_table_fields'])) {
					$po_item_fields = explode("\n", $_POST['nps_vendor_po_item_table_fields']);
					$po_item_fields = serialize($po_item_fields);
					$updateFields['po_item_table_field_map'] = $po_item_fields;
				}
				//set refresh to true
				$refresh = true;
				$append_url = 'v=' . $_POST['nps_vendor_id'] . '&btf=' . $_POST['btf'];

				//run update
				$this->_updateVendor($_POST['nps_vendor_id'], $updateFields);
			}
		}

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
	private function leftColumnHtml() {

		//set url parts
		$url = explode('?', $_SERVER['PHP_SELF']);
		$url_base = $url[0];
		if (!empty($url[1])) {
			$params = explode('&', $url[1]);
		} else {
			$params = array();
		}

		//title and list start
		$html = '<h2 style="border-bottom: 1px dotted #d9d9d9;font-size:15px;">Vendor Manager</h2>';
		$html .= '<ul id="nps-admin-vendor-manager-nav">';

		//vendor primary controls
		$html .= '<a href="' . $url_base . '?btf=1" title="Vendor Control Panel"><li class="' . $this->active(1, $this->btf) . '">Vendor Primary Settings</li></a>';

		//insert separator
		$html .= '<li class="separator"></li>';

		//close the list
		$html .= '</ul>';

		return $html;
	}

	private function npsVendorWelcomePage() {
		$html = '<h1>NPS Vendor Manager</h1>';
		$html .= '<p>Please select a function from the left</p>';
		return $html;
	}

	private function _selectVendor() {
		$html = '<h1>Select a Vendor</h1>';
		$html .= '<form id="nps_vendor_manager_select_vendor" name="nps_vendor_manager_select_vendor" method="get" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';
		$html .= '<select id="nps-vmanager-select-vendor" name="v" required><option></option>';
		foreach ($this->_getVendors() as $v) {
			$html .= '<option value="' . $v['id'] . '">' . $v['vendor_label'] . '</option>';
		}
		$html .= '</select>';
		$html .= '<input type="submit" style="margin-left: 35px;" value="Configure Vendor">';
		$html .= '<div class="clearer small noborder"></div>';
		$html .= '<input type="hidden" name="btf" value="1">';
		$html .= '</form>';

		return $html;
	}

	private function manageVendor() {
		//check for selected vendor
		if (empty($_GET['v'])) {
			//set output to the select vendor drop down
			$html = $this->_selectVendor();
		} else {
			//get vendor information
			$vendor = $this->_getVendor($_GET['v']);

			//start boody
			$html = '<h1>Configure ' . $vendor['vendor_label'] . ' Settings</h1>';
			$html .= '<form id="nps_vendor_options_update" name="nps_vendor_options_update" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';

			//vendor Identity
			$html .= '<div class="entry-edit">';
			$html .= '	<div class="entry-edit-head">';
			$html .= '		<h4 class="icon-head head-edit-form fieldset-legend">' . $vendor['vendor_label'] . ' Identity</h4>';
			$html .= '	</div>';
			$html .= '	<div class="fieldset" id="grop_fields">';
			$html .= '		<div class="half-block">';
			$html .= '			<label for="nps_vendor_id">Vendor ID <span class="page-head-note">(cannot be changed)</span></label>';
			$html .= '			<input type="text" disabled name="nps_vendor_id_display" value="' . $vendor['id'] . '">';
			$html .= '			<input type="hidden" name="nps_vendor_id" value="' . $vendor['id'] . '">';
			$html .= '		</div>';
			$html .= '		<div class="half-block last">';
			$html .= '			<label for="nps_vendor_identity">Vendor Identity <span class="page-head-note">(cannot be changed)</span></label>';
			$html .= '			<input type="text" disabled name="nps_vendor_identity_display" value="' . $vendor['vendor_id'] . '">';
			$html .= '			<input type="hidden" name="nps_vendor_identity" value="' . $vendor['vendor_id'] . '">';
			$html .= '		</div>';
			$html .= '		<div class="half-block">';
			$html .= '			<label for="nps_vendor_label">Vendor Label</label>';
			$html .= '			<input type="text" name="nps_vendor_label" value="' . $vendor['vendor_label'] . '">';
			$html .= '		</div>';
			$html .= '	<div class="clearer"></div>';
			$html .= '	</div>';
			$html .= '</div>';

			//Inventory Settings
			$html .= '<div class="entry-edit">';
			$html .= '	<div class="entry-edit-head">';
			$html .= '		<h4 class="icon-head head-edit-form fieldset-legend">' . $vendor['vendor_label'] . ' Inventory Settings</h4>';
			$html .= '	</div>';
			$html .= '	<div class="fieldset" id="grop_fields">';
			$html .= '		<div class="half-block">';
			$html .= '			<label for="nps_vendor_inv_file">Inventory Filename RegEx</label>';
			$html .= '			<input type="text" name="nps_vendor_inv_file" value="' . $vendor['file_name'] . '">';
			$html .= '			<div class="clearer"></div>';
			$html .= '			<p class="page-head-note">This should be the name of the file that gets imported into the DB for inventory.</p>';
			$html .= '		</div>';
			$html .= '		<div class="half-block no-border last standardize-children">';
			$html .= '			<div class="one-third std-me">';
			$html .= '				<label for="nps_vendor_inv_col_count">Total Column</label>';
			$html .= '				<input type="number" min="0" name="nps_vendor_inv_col_count" value="' . $vendor['inv_col_count'] . '">';
			$html .= '				<div class="clearer"></div>';
			$html .= '				<p class="page-head-note">Total number of columns in the inventory file</p>';
			$html .= '			</div>';
			$html .= '			<div class="one-third std-me">';
			$html .= '				<label for="nps_vendor_inv_qty_col">Quantity Column</label>';
			$html .= '				<input type="number" min="0" name="nps_vendor_inv_qty_col" value="' . $vendor['inv_qty_col'] . '">';
			$html .= '				<div class="clearer"></div>';
			$html .= '				<p class="page-head-note">Quantity column number</p>';
			$html .= '			</div>';
			$html .= '			<div class="one-third std-me no-border">';
			$html .= '				<label for="nps_vendor_inv_uid_col">Vendor UID Column</label>';
			$html .= '				<input type="number" min="0" name="nps_vendor_inv_uid_col" value="' . $vendor['inv_uid_col'] . '">';
			$html .= '				<div class="clearer"></div>';
			$html .= '				<p class="page-head-note">Vendor unique identifier column number</p>';
			$html .= '			</div>';
			$html .= '		</div>';
			$html .= '	<div class="clearer"></div>';
			$html .= '	</div>';
			$html .= '</div>';

			//purchase order info
			$html .= '<div class="entry-edit">';
			$html .= '	<div class="entry-edit-head">';
			$html .= '		<h4 class="icon-head head-edit-form fieldset-legend">' . $vendor['vendor_label'] . ' Purchase Order Settings</h4>';
			$html .= '	</div>';
			$html .= '	<div class="fieldset" id="grop_fields">';
			$html .= '		<div class="half-block">';
			$html .= '				<label for="nps_vendor_po_table">Purchase Order Table</label>';
			$html .= '				<input type="text" name="nps_vendor_po_table" value="' . $vendor['po_table'] . '">';
			$html .= '				<div class="clearer"></div>';
			$html .= '				<p class="page-head-note">This is the table that the purchase orders will be populated in</p>';
			$html .= '		</div>';
			$html .= '		<div class="half-block last">';
			$html .= '				<label for="nps_vendor_po_item_table">Purchase Order Items Table</label>';
			$html .= '				<input type="text" name="nps_vendor_po_item_table" value="' . $vendor['po_item_table'] . '">';
			$html .= '				<div class="clearer"></div>';
			$html .= '				<p class="page-head-note">This is the table that the purchase order items will be populated in</p>';
			$html .= '		</div>';
			$html .= '		<div class="clearer"></div>';
			$html .= '	</div>';
			$html .= '</div>';

			$html .= '<div class="entry-edit">';
			$html .= '	<div class="entry-edit-head">';
			$html .= '		<h4 class="icon-head head-edit-form fieldset-legend">' . $vendor['vendor_label'] . ' PO Table Fields</h4>';
			$html .= '	</div>';
			$html .= '	<div class="fieldset" id="grop_fields">';
			$html .= '		<p class="page-head-note">The following values define the table layout for the vendor purchase order and items table.</p>';
			$html .= '		<p class="page-head-note">Columns should be 1 per line</p>';
			$html .= '		<div class="half-block">';
			$html .= '				<label for="nps_vendor_po_table_fields">Purchase Order Table Fields</label>';

			//check for field map
			if (empty($vendor['po_table_field_map'])) {
				$vendor['po_table_field_map'] = serialize(array());
			}
			$html .= '				<textarea name="nps_vendor_po_table_fields" style="width: 50%;">' . implode("\n", unserialize($vendor['po_table_field_map'])) . '</textarea>';
			$html .= '		</div>';
			$html .= '		<div class="half-block last">';
			$html .= '				<label for="nps_vendor_po_item_table_fields">Purchase Order Items Table</label>';

			if (empty($vendor['po_item_table_field_map'])) {
				$vendor['po_item_table_field_map'] = serialize(array());
			}
			$html .= '				<textarea name="nps_vendor_po_item_table_fields" style="width: 50%;">' . implode("\n", unserialize($vendor['po_item_table_field_map'])) . '</textarea>';
			$html .= '		</div>';
			$html .= '		<div class="clearer"></div>';
			$html .= '	</div>';
			$html .= '</div>';

			//include hidden form key and function command
			$html .= '<input type="hidden" name="btf" value="1">';
			$html .= '<input type="hidden" name="nps_function" value="vendor_manager_core_settings">';
			$html .= '<input type="hidden" name="nps_value_updated" value="false">';
			$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';

			//submit button
			$html .= '<div class="clearer"></div>';
			$html .= '<input type="submit" value="Save Vendor Options">';
			$html .= '</form>';

			//include the javascript file
			$DS = DIRECTORY_SEPARATOR;
			$html .= '<script>' . file_get_contents(Mage::getBaseDir('base') . $DS . 'app' . $DS . 'code' . $DS . 'local' . $DS . 'NPS' . $DS . 'VendorManager' . $DS . 'Helper' . $DS . 'vendorManagerControl.js') . '</script>';
		}

		return $html;
	}

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
	public function _getVendors($where = null) {

		//start base query
		$query = 'SELECT `id`, `vendor_id`, `file_name`, `inv_uid_col`, `inv_qty_col`, `inv_col_count`, `vendor_label`, `po_table`, `po_item_table` FROM `nps_vendor`';

		if (!empty($where)) {
			$query .= $where;
		}

		$this->sqlread->query($query);
		$results = $this->sqlread->fetchAll($query);
		return $results;
	}
	public function _updateVendor($vendor_id, $fields = array()) {
		if (!empty($fields)) {
			$updates = array();
			foreach ($fields as $col => $val) {
				$updates[] = "`" . $col . "` = '" . $val . "'";
			}
			$update_values = implode(",", $updates);
			$query = "UPDATE `nps_vendor` SET " . $update_values . " WHERE `id` = " . $vendor_id;
			$this->sqlwrite->query($query);
		}
	}

/**
INFASTRUCTURE METHODS
 */

	private function setNPSClassVars() {
		$this->page_cookie = base64_encode('pagemessages');
		$this->dwa_cookie = 'nps_default_working_attribute';
		$this->dwa_delim = '-';
		$this->dwa_select = null;
		$this->dwa_value_array = null;
		$this->dwa_id = null;
		$this->dwa_code = null;

		//check and set cookie vars
		if (!empty($_COOKIE[$this->dwa_cookie])) {
			$this->dwa_select = $_COOKIE[$this->dwa_cookie];
			$this->dwa_value_array = explode($this->dwa_delim, $_COOKIE[$this->dwa_cookie]);
			$this->dwa_id = $this->dwa_value_array[0];
			$this->dwa_code = $this->dwa_value_array[1];
		}
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
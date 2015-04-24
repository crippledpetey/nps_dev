<?php

class NPS_CustomAdminFunctions_IndexController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

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
			'npsWelcomePage',
			'createAddOptionsAttributeForm',
			'customAttributeOptions',
			'autoOrderAttributes',
			'blankAttrRemover',
			'removeOptionsAttributeForm',
		);

		//set the primary display content
		$primaryContent = '<style>' . file_get_contents(Mage::getBaseDir('base') . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . 'NPS' . DIRECTORY_SEPARATOR . 'CustomAdminFunctions' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'adminStyle.css') . '</style>';
		$primaryContent .= '<div id="nps-custom-attr-manager-container">' . call_user_func(array($this, $displayModes[$this->btf])) . '</div>';

		//load the layout
		$this->loadLayout();

		//set the menu item active
		$this->_setActiveMenu('catalog/nps_attribute_manager_menu');

		//set left block
		$leftBlock = $this->getLayout()
		                  ->createBlock('core/text')
		                  ->setText($this->leftColumnHtml());

		//compile the lyout
		$block = $this->getLayout()
		              ->createBlock('core/text', 'nps-custom-attr-control-panel')
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

		//check for default attribute value
		if (isset($_POST['nps_set_default_attribute'])) {
			if ((empty($_COOKIE[$this->dwa_cookie])) || ($_COOKIE[$this->dwa_cookie] !== $_POST['nps_set_default_attribute'])) {
				setcookie($this->dwa_cookie, $_POST['nps_set_default_attribute'], 0, '/');
				$refresh = true;
			}
		}

		//check if form is subitted
		if (isset($_POST['nps_function'])) {

			//if mass attribute addition
			if ($_POST['nps_function'] == 'mass_attr_option_add') {

				//append the btf number to the url
				$append_url = 'btf=1';

				//set the attribute name
				$attr_code = $_POST['nps_attr_select'];
				if (!empty($attr_code) && $attr_code !== '') {

					//$start sorting
					$sort_start = $_POST['nps_attr_start_number'];

					//set the options
					$attr_options = $_POST['nps_attr_new_options'];
					$attr_options = explode(',', $_POST['nps_attr_new_options']);

					//process and remove any blanks prior to processing
					foreach ($attr_options as $key => $option) {if ($option == '' || empty($option)) {unset($attr_options[$key]);}}

					//process new options
					$this->addAttributeOptions($attr_code, $attr_options, $sort_start);

					//trigger page refresh
					$refresh = true;
				}

			} elseif ($_POST['nps_function'] == 'nps_attr_option_settings') {
				//set page message
				Mage::getSingleton('adminhtml/session')->addSuccess('Options for <span style="color:#999;font-style:italic;">' . strtoupper($_POST['attribute_id']) . '</span> have been updated');

				//get the post type anomolies
				$checkboxes = $this->attrOptionCheckboxes();
				$multiple = $this->attrOptionMultiSelect();

				//get default page options
				$pageOptions = $this->getAttrOptionDefaults();
				foreach ($pageOptions as $key => $default) {
					//check if checkbox
					if (in_array($key, $checkboxes)) {
						if (isset($_POST[$key]) && $_POST[$key] == 'on') {
							$pageOptions[$key] = 1;
						} else {
							$pageOptions[$key] = 0;
						}
					} elseif (in_array($key, $multiple)) {
						if (!empty($_POST[$key])) {
							$option_value = array();
							foreach ($_POST[$key] as $k => $p) {
								$option_value[] = $p;
							}
							$pageOptions[$key] = $option_value;
						}
					} else {
						if (!empty($_POST[$key])) {
							$pageOptions[$key] = preg_replace("/\r|\n/", "", $_POST[$key]);
						}
					}
				}

				//update the records
				$this->setAttributeOptions($_POST['attribute_id_num'], $pageOptions);

				//set the refresh url
				$append_url = 'btf=2&attr=' . $_POST['attribute_id'];

				//trigger page refresh
				$refresh = true;
			} elseif ($_POST['nps_function'] == 'attr_option_reorder') {
				//default numeric value
				$numeric = false;
				//check for numeric trigger
				if (isset($_POST['attr_reorder_is_numeric'])) {
					$numeric = true;
				}

				//check for fraction trigger
				$fraction = false;
				if (isset($_POST['attr_reorder_is_fraction']) && $_POST['attr_reorder_is_fraction'] == 'on') {
					$numeric = false;
					$fraction = true;
				}

				//reorder options
				$this->reorderOptions($_POST['nps_attr_select'], $numeric, $fraction);
				//set the refresh url
				$append_url = 'btf=3';
				//trigger page refresh
				$refresh = true;
			} elseif ($_POST['nps_function'] == 'attr_option_remove_blank') {

				//reorder options
				$this->removeBlankOptions($_POST['nps_attr_select']);
				//set the refresh url
				$append_url = 'btf=4';
				//trigger page refresh
				$refresh = true;
			} elseif ($_POST['nps_function'] == 'mass_attr_option_remove') {
				if (!empty($_POST['nps_attr_remove_options']) && $_POST['nps_attr_remove_options'] !== '') {

					//reorder options
					$this->removeSelectedOptions($_POST['nps_attr_select'], $_POST['nps_attr_remove_options']);

					//set the refresh url
					$append_url = 'btf=5';

					//trigger page refresh
					$refresh = true;
				}
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
		$html = '<h2 style="border-bottom: 1px dotted #d9d9d9;font-size:15px;">NPS Custom Attribute Tools</h2>';
		$html .= '<ul id="nps-admin-custom-attr-nav">';

		//attribute controls
		$html .= '<a href="' . $url_base . '?btf=2" title="Attribute Controls"><li class="' . $this->active(2, $this->btf) . '">Attribute Control Panel</li></a>';

		//insert separator
		$html .= '<li class="separator"></li>';

		//mass add options
		$html .= '<a href="' . $url_base . '?btf=1" title="Mass Add Attribute Options"><li class="' . $this->active(1, $this->btf) . '">Mass Add Attribute Options</li></a>';

		//Remove Blank Values En Mass
		$html .= '<a href="' . $url_base . '?btf=5" title="Remove Specific Options"><li class="' . $this->active(5, $this->btf) . '">Mass Remove Attribute Options</li></a>';

		//Remove Blank Values
		$html .= '<a href="' . $url_base . '?btf=4" title="Remove Blank Options"><li class="' . $this->active(4, $this->btf) . '">Remove Blank Attribute Options</li></a>';

		//auto order
		$html .= '<a href="' . $url_base . '?btf=3" title="Auto Order Attribute Options"><li class="' . $this->active(3, $this->btf) . '">Autorder Attribute Options</li></a>';

		//close the list
		$html .= '</ul>';

		//set default attribute
		$html .= '<div id="nps-control-nav-bottom">';
		$html .= '	<h3>Set Default Attribute</h3>';
		$html .= '	<form id="set_working_attribute_form" enctype="multipart/form-data" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
		$html .= '		<select id="nps_set_default_attribute" name="nps_set_default_attribute" style="width: 95%;"><option value="unset"' . $this->selected(null, $this->dwa_select) . '></option>';
		$html .= implode('', $this->getAttributesForSelect('both', $this->dwa_select));
		$html .= '		</select>';
		$html .= '		<div class="clearer small noborder"></div>';
		$html .= '		<input type="submit" value="Set Working Attribute">';
		$html .= '		<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';
		$html .= '	</form>';
		$html .= '</div>';

		return $html;
	}

	private function npsWelcomePage() {
		$html = '<h1>NPS Custom Attribute Tools</h1>';
		$html .= '<p>Please select a function from the left</p>';
		return $html;
	}

	private function blankAttrRemover() {
		//start boody
		$html = '<h1>Auto Remove Blank Options</h1>';
		$html .= '<form id="nps_attr_option_select_attr_remove_blank" name="nps_attr_option_select_attr_remove_blank" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';

		//include hidden form key and function command
		$html .= '<input type="hidden" name="btf" value="4">';
		$html .= '<input type="hidden" name="nps_function" value="attr_option_remove_blank">';
		$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';

		$html .= '<label for="nps_attr_select">Select Attribute</label>';
		$html .= '<select name="nps_attr_select" required><option></option>';

		//get the list of attribute that can have options selected
		$html .= implode(null, $this->getAttributesForSelect('code', $this->dwa_code));

		//close select box
		$html .= '</select><div class="clearer small noborder"></div>';

		//submit button
		$html .= '<input type="submit" value="Remove Blank Options">';

		return $html;
	}

	private function autoOrderAttributes() {
		//start boody
		$html = '<h1>Auto Order Attribute Options</h1>';
		$html .= '<form id="nps_attr_option_select_attr_reorder" name="nps_attr_option_select_attr_reorder" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';

		//include hidden form key and function command
		$html .= '<input type="hidden" name="btf" value="3">';
		$html .= '<input type="hidden" name="nps_function" value="attr_option_reorder">';
		$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';

		$html .= '<label for="nps_attr_select">Select Attribute</label>';
		$html .= '<select name="nps_attr_select" required><option></option>';

		//get the list of attribute that can have options selected
		$html .= implode('', $this->getAttributesForSelect('id', $this->dwa_id));

		//close select box
		$html .= '</select><div class="clearer small noborder"></div>';
		//is numeric checkbox
		$html .= '<div class="half-block">';
		$html .= '<label for="attr_reorder_is_numeric">Field is Numeric</label>';
		$html .= '<input type="checkbox" name="attr_reorder_is_numeric">';
		$html .= '</div>';

		//is fraction checkbox
		$html .= '<div class="half-block">';
		$html .= '<label for="attr_reorder_is_fraction">Field is Fraction</label>';
		$html .= '<input type="checkbox" name="attr_reorder_is_fraction">';
		$html .= '</div>';
		$html .= '<div class="clearer big"></div>';

		//submit button
		$html .= '<input type="submit" value="Update Attribute">';

		return $html;
	}

	private function removeOptionsAttributeForm() {

		//start html output
		$html = '<h1>Mass Attribute Option Removal</h1>';

		//explanation
		$html .= '<p class="page-head-note">Youb can use this tool to quickly remove a grouping of options for a given attribute.</p>';
		$html .= '<p class="page-head-note">Select the attribute from the list and enter a comma separated list of option values that you would like to remove.</p>';

		//start form
		$html .= '<form id="nps_mass_attr_option_remove" name="nps_mass_attr_option_remove" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';

		//include hidden form key and function command
		$html .= '<input type="hidden" name="nps_function" value="mass_attr_option_remove">';
		$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';

		//start select box
		$html .= '<div class="half-block">';
		$html .= '<label for="nps_attr_select">Select Attribute</label>';
		$html .= '<select name="nps_attr_select" required value=""><option>SELECT ATTRIBUTE</option>';

		//get the list of attribute that can have options selected
		$html .= implode('', $this->getAttributesForSelect('code', $this->dwa_code));

		//close select box
		$html .= '</select>';
		$html .= '</div>';

		//add text area for adding comma separated values
		$html .= '<label for="nps_attr_remove_options" class="full-width" style="display: block;">Comma Separated Values</label>';
		$html .= '<textarea id="nps_attr_remove_options" name="nps_attr_remove_options" class="full-width" required></textarea><br>';

		//submit button
		$html .= '<input type="submit" value="Remove Attribute Options">';

		//close form
		$html .= '</form>';

		return $html;
	}

	private function createAddOptionsAttributeForm() {

		//start html output
		$html = '<h1>Mass Add Attribute Options</h1>';

		//explanation
		$html .= '<p class="page-head-note">This area will allow you to insert a massive amount of attribute options for a selected attribute. Enter a comma separated list of values below to add them to the attribute you select. It is imperative that you <span style="color:red;font-weight:bold;">DO NOT</span> insert values that have comma in them or you will confuse the system and cause undesired results.</p>';
		$html .= '<p class="page-head-note">Options should not be duplicated and so any from your list that exist in the database will not be inserted. It should be noted that options that are inserted will be given sort ordering starting with 0 and progressing through the group of options. If you would prefer that the ordering start at a different number select it below.</p>';
		$html .= '<p class="page-head-note">You may only select options that are either drop down or multiselect</p>';

		//start form
		$html .= '<form id="nps_mass_attr_option_add" name="nps_mass_attr_option_add" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';

		//include hidden form key and function command
		$html .= '<input type="hidden" name="nps_function" value="mass_attr_option_add">';
		$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';

		//start select box
		$html .= '<div class="half-block">';
		$html .= '<label for="nps_attr_select">Select Attribute</label>';
		$html .= '<select name="nps_attr_select" required value=""><option>SELECT ATTRIBUTE</option>';

		//get the list of attribute that can have options selected
		$html .= implode('', $this->getAttributesForSelect('code', $this->dwa_select));

		//close select box
		$html .= '</select>';
		$html .= '</div>';

		//start number
		$html .= '<div class="half-block">';
		$html .= '<label for="nps_attr_start_number">Start Ordering From</label>';
		$html .= '<input type="number" name="nps_attr_start_number" value="0"><br>';
		$html .= '</div><div class="clearer"></div>';

		//add text area for adding comma separated values
		$html .= '<label for="nps_attr_new_options" class="full-width" style="display: block;">Comma Separated Values</label>';
		$html .= '<textarea id="nps_attr_new_options" name="nps_attr_new_options" class="full-width" required></textarea><br>';

		//submit button
		$html .= '<input type="submit" value="Update Attribute">';

		//close form
		$html .= '</form>';

		return $html;
	}

	private function customAttributeOptions() {
		//start boody
		$html = '<h1>Custom Attribute Options</h1>';

		//check for attribute being set
		if (!isset($_GET['attr'])) {

			$html .= '<form id="nps_attr_option_select_attr" name="nps_attr_option_select_attr" method="get" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';
			$html .= '<input type="hidden" name="btf" value="2">';

			$html .= '<label for="nps_attr_select">Select Attribute</label>';
			$html .= '<select name="attr" required>';

			//get the list of attribute that can have options selected
			$html .= implode('', $this->getAttributesForSelect('code', $this->dwa_code));

			//close select box
			$html .= '</select><div class="clearer big"></div>';

			$submit_buttom = 'Configure Attribute';

		} else {
			$attribute_id = $_GET['attr'];

			//start form & include hidden form key and function command
			$html .= '<form id="nps_attr_options" name="nps_attr_options" method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">';
			$html .= '<input type="hidden" name="nps_function" value="nps_attr_option_settings">';
			$html .= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '">';
			$html .= '<input type="hidden" name="attribute_id" value="' . $attribute_id . '">';

			//show the attribute ID
			$html .= '<div class="half-block">';
			$html .= '<label for="nps_attr_select">Attribute Code</label>';
			$html .= '<input type="text" disabled name="attribute_code_display" value="' . $attribute_id . '">';
			$html .= '</div>';

			if ($attributeRaw = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_id, 'attribute_id')) {

				//get existing values
				$existingArray = $this->getAttrOptionDefaults();
				$dbValues = $this->getAttributeOptions($attributeRaw->getId());
				if ($dbValues) {
					$dbArray = json_decode($dbValues['options'], true);
					//verify all values exist and set default if not (prevents error on change of available values)
					foreach ($existingArray as $key => $value) {
						if (!empty($dbArray[$key])) {
							$existingArray[$key] = $dbArray[$key];
						}
					}
				}

				//hidden attribute ID number
				$html .= '<input type="hidden" name="attribute_id_num" value="' . $attributeRaw->getId() . '">';

				//var_dump($attributeRaw);
				$html .= '<div class="half-block">';
				$html .= '<label for="nps_attr_select">Attribute ID</label>';
				$html .= '<input type="text" disabled name="attribute_id_display" value="' . $attributeRaw->getId() . '">';
				$html .= '</div>';

				$html .= '<div class="clearer small"></div>';

				//carry child up to container products
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_carry_parent">Carry Over to Parent Container Product</label>';
				$html .= '<input type="checkbox" name="attr_option_carry_parent"' . $this->checked($existingArray['attr_option_carry_parent'], 1) . '><br>';
				$html .= '</div>';

				//how to handle duplicates
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_duplicate_handling">Duplicate Handling</label>';
				$html .= '<select name="attr_option_duplicate_handling"><option value=""' . $this->selected($existingArray['attr_option_duplicate_handling'], null) . '></option>';
				$html .= '<option value="override"' . $this->selected($existingArray['attr_option_duplicate_handling'], 'override') . '>Override (newest products value is used)</option>';
				$html .= '<option value="append"' . $this->selected($existingArray['attr_option_duplicate_handling'], 'append') . '>Append Values (creates a comma separated list)</option>';
				$html .= '<option value="hide"' . $this->selected($existingArray['attr_option_duplicate_handling'], 'hide') . '>Hide All Values (Hides all values if they differ)</option>';
				$html .= '<option value="popular"' . $this->selected($existingArray['attr_option_duplicate_handling'], 'popular') . '>Most Popular (Displays the value that appears most)</option>';
				$html .= '</select>';
				$html .= '</div>';

				$html .= '<div class="clearer small"></div>';

				//add to product description section
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_add_prd_desc">Add to Product Description Section</label>';
				$html .= '<input type="checkbox" name="attr_option_add_prd_desc" ' . $this->checked($existingArray['attr_option_add_prd_desc'], 1) . '>';
				$html .= '</div>';

				//display regions section start
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_desc_location">Description Regions</label>';

				//get the display locations
				$display_locations = $existingArray['attr_option_desc_location'];
				$html .= '<select name="attr_option_desc_location[]" multiple size="5">';
				//loop through values
				foreach ($this->getProductDescriptionZones() as $key => $value_array) {
					$sVal = null;
					if (in_array($key, $display_locations)) {
						$sVal = ' selected ';
					}
					$html .= '<option value="' . $key . '"' . $sVal . '>' . $value_array['label'] . '</option>';
				}
				$html .= '</select>';
				$html .= '</div>';

				$html .= '<div class="clearer small"></div>';

				//unit of measurement
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_add_uom">Unit of Measurement</label>';
				$html .= '<input type="text" name="attr_option_add_uom" value="' . $existingArray['attr_option_add_uom'] . '">';
				$html .= '</div>';

				//omission values
				$html .= '<div class="half-block">';
				$html .= '<label for="attr_option_add_uom">Omission Settings</label>';
				$html .= 'Active: <input type="checkbox" name="attr_option_omit_active" ' . $this->checked($existingArray['attr_option_omit_active'], 1) . '>';
				$html .= '<br>Values: <input type="text" name="attr_option_omit_values" value="' . $existingArray['attr_option_omit_values'] . '">';
				$html .= '</div>';

				$html .= '<div class="clearer medium"></div>';

				//description region controls
				$html .= '<div class="full-width">';
				$html .= '<h3>Product Page Content Display Controls</h3>';
				$html .= '<p class="page-head-note">The following sections control the way the information will be output on the product page. If you have any questions about usage or output please speak with the development team.</p>';

				//output control section for different description regions
				foreach ($this->getProductDescriptionZones() as $key => $value_array) {

					//default checkbox values
					$default_vals = $value_array['defaults'];

					//html output
					$html .= '<div id="nps-attr-desc-control-' . $key . '" class="nps-attr-desc-controls">';
					$html .= '	<label class="full-width area-toggle"><a>' . $value_array['label'] . '</a></label>';
					$html .= '	<div class="nps-attr-desc-control-content">';
					$html .= '		<div class="half-block">';
					$html .= '			<div class="half-block">';
					$html .= '				<label for="nps_attr_option_' . $key . '_inlist">Show Specification in List</label>';
					$html .= '				<input type="checkbox" name="nps_attr_option_' . $key . '_inlist"' . $this->checked($existingArray['nps_attr_option_' . $key . '_inlist'], true) . '><div class="clearer"></div>';
					$html .= '				<p class="page-head-note">Checking this will populate the spec name and value in the generated bulleted list in the description region.</p>';
					$html .= '			</div>';
					$html .= '			<div class="half-block">';
					$html .= '				<label for="nps_attr_option_' . $key . '">Add Display Content</label>';
					$html .= '				<input type="checkbox" name="nps_attr_option_' . $key . '_display_content"' . $this->checked($existingArray['nps_attr_option_' . $key . '_display_content'], true) . '><div class="clearer"></div>';
					$html .= '				<p class="page-head-note">Checking this will add the content from below to the body of the description region.</p>';
					$html .= '			</div>';
					$html .= '		</div>';
					$html .= '		<div class="half-block">';
					$html .= '			<div class="half-block">';
					$html .= '				<label for="nps_attr_option_' . $key . '_priority">Display Priority</label>';
					$html .= '				<input type="number" name="nps_attr_option_' . $key . '_priority" value="' . $existingArray['nps_attr_option_' . $key . '_priority'] . '"><div class="clearer"></div>';
					$html .= '				<p class="page-head-note">Controls where the spec and description body content are displayed in relation to other attribute content. The higher the number the higher it will be shown.</p>';
					$html .= '			</div>';
					$html .= '			<div class="half-block">';
					//$html .= '				<label for="nps_attr_option_' . $key . '"></label>';
					//$html .= '				<input type="checkbox" name="nps_attr_option_' . $key . '" checked=""><div class="clearer"></div>';
					//$html .= '				<p class="page-head-note"></p>';
					$html .= '			</div>';
					$html .= '		</div>';
					$html .= '		<div class="clearer small noborder"></div>';
					$html .= '		<label for="nps_attr_option_' . $key . '_list_supp">List Supplement Text</label>';
					$html .= '		<div class="clearer small noborder"></div>';
					$html .= '		<p class="page-head-note">This content will be appended to the end of the value in the generated list</p>';
					$html .= '		<input type="text" id="nps_attr_option_' . $key . '_list_supp" name="nps_attr_option_' . $key . '_list_supp" class="full-width" value="' . $existingArray['nps_attr_option_' . $key . '_list_supp'] . '">';
					$html .= '		<div class="clearer small noborder"></div>';
					$html .= '		<label for="nps_attr_option_' . $key . '_description">Attribute Description</label>';
					$html .= '		<div class="clearer small noborder"></div>';
					$html .= '		<p class="page-head-note">This content will be displayed on hover of the name in multiple locations on the site</p>';
					$html .= '		<textarea id="nps_attr_option_' . $key . '_description" name="nps_attr_option_' . $key . '_description" class="full-width">' . $existingArray['nps_attr_option_' . $key . '_description'] . '</textarea><br>';
					$html .= '	</div>';
					$html .= '</div>';
					$html .= '<script type="text/javascript">jQuery(document).ready(function(s){s("#nps-attr-desc-control-' . $key . ' .area-toggle").click(function(){s(this).hasClass("exposed")?(s(this).siblings(".nps-attr-desc-control-content").slideUp(),s(this).removeClass("exposed")):(s(this).siblings(".nps-attr-desc-control-content").slideDown(),s(this).addClass("exposed"))})});</script>';
				}

				$html .= '</div>';

			} else {
				//cant find the attribute
				$html .= '<h2 style="color: red; text-transform:uppercase;">we\'re sorry but there was an error. please go back and try again by selecting another attirbute.</h2>';
			}

			$html .= '<div class="clearer big noborder"></div>';

			//submit button value
			$submit_buttom = 'Save Attribute';
		}

		//submit button
		$html .= '<input type="submit" value="' . $submit_buttom . '">';

		//close form
		$html .= '</form>';

		return $html;
	}

/**
DATABASE AND OTHER UPDATE METHODS CALLED BY  $this->requestFunctions()
 */
	protected function addAttributeOptions($attribute_code, array $optionsArray, $sort_start) {

		//database read adapter
		$this->setConnection();

		//make sure we can find the attribute
		if ($attributeRaw = Mage::getSingleton("eav/config")->getAttribute('catalog_product', $attribute_code, 'attribute_id')) {

			//get and clean existing options
			$attribute_existing = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attribute_code);
			if ($attribute_existing->usesSource()) {

				//compile the options
				$options = $attribute_existing->getSource()->getAllOptions(false);

				//loop through and remove any duplicates that exist from previous
				foreach ($options as $key => $val) {
					if ($new_key = array_search($val['label'], $optionsArray)) {
						unset($optionsArray[$new_key]);
					}
				}
			}

			if (!empty($optionsArray)) {

				$attributeData = $attributeRaw->getData();
				$attributeId = $attributeData['attribute_id'];
				$recordCount = 0;

				foreach ($optionsArray as $sortOrder => $label) {
					// add option
					$data = array(
						'attribute_id' => $attributeId,
						'sort_order' => $sortOrder + $sort_start,
					);
					$this->sqlwrite->insert('eav_attribute_option', $data);

					// add option label
					$optionId = (int) $this->sqlread->lastInsertId('eav_attribute_option', 'option_id');
					$data = array(
						'option_id' => $optionId,
						'store_id' => 0,
						'value' => $label,
					);
					$this->sqlwrite->insert('eav_attribute_option_value', $data);
					$recordCount++;
				}
				//set success message
				Mage::getSingleton('adminhtml/session')->addSuccess('Successfully added <span style="color:#999;font-style:italic;">' . $recordCount . '</span> options to the <span style="color:#999;font-style:italic;">' . strtoupper($_POST['nps_attr_select']) . '</span> Attribute');
			} else {
				Mage::getSingleton('adminhtml/session')->addNotice('Warning; no options were added. This could be because they submitted options would duplicate existing ones. If you think this to be incorrect please see the development team for assistance. ERROR NO:SCVOW74EMVOCGFD');
			}
		}
	}
	protected function getAttributeOptions($attribute_id) {
		//verify connection is there
		if (!isset($this->sqlwrite)) {
			$this->setConnection();
		}
		//check for existing option
		$select = $this->sqlwrite->select()->from('nps_attribute_options', array('id', 'attribute_id', 'options', 'parent_show', 'desc_show'))->where('attribute_id=?', $attribute_id);
		$rowsArray = $this->sqlread->fetchRow($select);
		return $rowsArray;
	}

	protected function setAttributeOptions($attribute_id, $option_array) {

		//check for parent and description values
		$parent_show = false;
		$description_show = false;
		if ($option_array['attr_option_carry_parent']) {
			$parent_show = true;
		}
		if ($option_array['attr_option_add_prd_desc']) {
			$description_show = true;
		}

		//serialize data
		$serialized = json_encode($option_array);

		//verify connection is there
		if (!isset($this->sqlread)) {
			$this->setConnection();
		}

		//start transaction
		$this->sqlwrite->beginTransaction();

		//check for existing
		if (!empty($this->getAttributeOptions($attribute_id))) {
			//set update fields
			$update_fields = array();
			$update_fields['options'] = $serialized;
			$update_fields['parent_show'] = (string) $parent_show;
			$update_fields['desc_show'] = (string) $description_show;
			$update_where = $this->sqlwrite->quoteInto('attribute_id=?', $attribute_id);
			$this->sqlwrite->update('nps_attribute_options', $update_fields, $update_where);
		} else {
			//set insert fields
			$insert_fields = array();
			$insert_fields['options'] = $serialized;
			$insert_fields['attribute_id'] = $attribute_id;
			$insert_fields['parent_show'] = (string) $parent_show;
			$insert_fields['desc_show'] = (string) $description_show;
			$this->sqlwrite->insert('nps_attribute_options', $insert_fields);
		}
		//commit the transaction
		$this->sqlwrite->commit();
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

			if (!empty($this->dwa_value_array[1])) {
				$this->dwa_id = $this->dwa_value_array[0];
			} else {
				$this->dwa_id = null;
			}

			if (!empty($this->dwa_value_array[1])) {
				$this->dwa_code = $this->dwa_value_array[1];
			} else {
				$this->dwa_code = null;
			}

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
	public function getProductDescriptionZones() {
		return array(
			'specs' => array(
				'label' => 'Key Specifications',
				'defaults' => array(
					'show_spec' => true,
					'show_desc' => false,
					'show_list_supp' => null,
				),
			),
			'feat' => array(
				'label' => 'Features',
				'defaults' => array(
					'show_spec' => true,
					'show_desc' => true,
					'show_list_supp' => null,
				),
			),
			'tech' => array(
				'label' => 'Manufacturer Technologies',
				'defaults' => array(
					'show_spec' => true,
					'show_desc' => true,
					'show_list_supp' => null,
				),
			),
			'maint' => array(
				'label' => 'Maintenance',
				'defaults' => array(
					'show_spec' => false,
					'show_desc' => false,
					'show_list_supp' => null,
				),
			),
		);
	}
	private function attrOptionMultiSelect() {
		$return = array(
			'attr_option_desc_location',
		);
		return $return;
	}
	private function attrOptionCheckboxes() {
		//base returns
		$return = array(
			'attr_option_carry_parent',
			'attr_option_add_prd_desc',
			'attr_option_omit_active',
		);

		//get the description zones
		$attr_prd_desc_attr = $this->getProductDescriptionZones();
		foreach ($attr_prd_desc_attr as $key => $default) {
			$return[] = 'nps_attr_option_' . $key . '_inlist';
			$return[] = 'nps_attr_option_' . $key . '_display_content';
		}
		return $return;

	}
	private function getAttrOptionDefaults() {

		//base returns
		$return = array(
			'attribute_id' => null,
			'attr_option_carry_parent' => null,
			'attr_option_duplicate_handling' => null,
			'attr_option_add_prd_desc' => null,
			'attr_option_add_uom' => null,
			'attr_option_desc_location' => array(),
			'attr_option_omit_active' => false,
			'attr_option_omit_values' => 'No,N\/A',
		);

		//get the description zones
		$attr_prd_desc_attr = $this->getProductDescriptionZones();
		foreach ($attr_prd_desc_attr as $key => $default) {
			$return['nps_attr_option_' . $key . '_inlist'] = $default['defaults']['show_spec'];
			$return['nps_attr_option_' . $key . '_list_supp'] = $default['defaults']['show_list_supp'];
			$return['nps_attr_option_' . $key . '_display_content'] = $default['defaults']['show_desc'];
			$return['nps_attr_option_' . $key . '_priority'] = 0;
			$return['nps_attr_option_' . $key . '_description'] = null;
		}
		return $return;
	}
	protected function reorderOptions($attribute_id, $is_numeric = true, $is_fraction = false) {

		//verify connection is there
		if (!isset($this->sqlwrite)) {
			$this->setConnection();
		}

		//check for numeric flag
		$order_by = 'v.value';
		if ($is_numeric) {
			$order_by = 'CAST(v.value AS DECIMAL (12 , 4 ))';
		}

		//check for fraction
		if ($is_fraction) {
			$reorder_select = "SELECT core.option_id, upd.value FROM nps_dev.eav_attribute_option AS core INNER JOIN (SELECT v.value, o.option_id, attribute_id, CAST( CASE WHEN v.value NOT LIKE '%/%' THEN v.value ELSE CAST(CAST(REPLACE(LEFT(v.value, LOCATE('/', v.value, 1) - 1), CONCAT(LEFT(v.value, LOCATE(' ', v.value, 1) - 1), ' '), '') AS DECIMAL (12 , 4 )) / CAST(REPLACE(v.value, LEFT(v.value, LOCATE('/', v.value, 1)), '') AS DECIMAL (12 , 4 )) + CAST(LEFT(v.value, LOCATE(' ', v.value, 1) - 1) AS DECIMAL (12 , 4 )) AS DECIMAL (12 , 4 )) END AS DECIMAL(12,4) ) AS `decimal` FROM nps_dev.eav_attribute_option AS o INNER JOIN nps_dev.eav_attribute_option_value AS v ON o.option_id = v.option_id WHERE attribute_id = " . $attribute_id . " ORDER BY `decimal` ASC) AS upd ON upd.option_id = core.option_id, (SELECT @n:=0) m";
		} else {

			//base update select for all string values that aren't fractions
			$reorder_select = "SELECT core.option_id, upd.value FROM nps_dev.eav_attribute_option AS core INNER JOIN (SELECT  v.value, o.option_id, @n:=@n + 1 AS 'new_order' FROM nps_dev.eav_attribute_option AS o INNER JOIN nps_dev.eav_attribute_option_value AS v ON o.option_id = v.option_id, (SELECT @n:=0) m WHERE attribute_id = " . $attribute_id . " ORDER BY " . $order_by . " ASC) AS upd ON upd.option_id = core.option_id";
		}

		//insert options into temp table
		$query_1 = "INSERT INTO nps_dev.eav_attribute_reorder_temp (`option_id`, `value`) " . $reorder_select;
		//set counter
		$query_2 = "SELECT @i := 0;";
		//set values based on counter
		$query_3 = "UPDATE nps_dev.eav_attribute_reorder_temp set new_order = (select @i := @i + 1) WHERE id IS NOT NULL";
		//update actual order
		$query_4 = "UPDATE nps_dev.eav_attribute_option AS CORE INNER JOIN nps_dev.eav_attribute_reorder_temp AS UPD ON UPD.option_id = CORE.option_id SET CORE.sort_order = UPD.new_order WHERE CORE.sort_order <> UPD.new_order";
		//delete all entires from temp table
		$query_5 = "DELETE FROM nps_dev.eav_attribute_reorder_temp";

		//run queries
		$this->sqlwrite->query($query_1);
		$this->sqlwrite->query($query_2);
		$this->sqlwrite->query($query_3);
		$this->sqlwrite->query($query_4);
		$this->sqlwrite->query($query_5);

		//set success message
		Mage::getSingleton('adminhtml/session')->addSuccess('Reordered options');
	}

	protected function removeBlankOptions($attribute_code) {

		$attribute = Mage::getModel('catalog/resource_eav_attribute')
			->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attribute_code);

		$optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attribute->getAttributeId())
			->setPositionOrder('desc', true)
			->load();

		$count = 0;
		foreach ($optionCollection as $option) {
			//remove if options value is empty
			if ($option->getValue() == '' || $option->getValue() == ' ' || $option->getValue() == '	' || str_replace(' ', null, $option->getValue()) == '') {
				$option->delete();
				$count++;
			}
		}

		Mage::getSingleton('adminhtml/session')->addSuccess('Removed ' . $count . ' blank options');
	}

	protected function removeSelectedOptions($attribute_code, $data) {

		$toBeRemoved = explode(',', $data);
		$attribute = Mage::getModel('catalog/resource_eav_attribute')
			->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attribute_code);

		$optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attribute->getAttributeId())
			->setPositionOrder('desc', true)
			->load();

		$count = 0;
		foreach ($optionCollection as $option) {
			//remove if options value is empty
			if (in_array($option->getValue(), $toBeRemoved)) {
				$option->delete();
				$count++;
			}
		}

		Mage::getSingleton('adminhtml/session')->addSuccess('Removed ' . $count . ' options');
	}
	private function getAttributesForSelect($value_type = 'code', $checkValue = null, $separator = '-') {
		require_once Mage::getBaseDir('base') . '/app/code/local/NPS/BetterLayerNavigation/Helper/product.view.class.php';
		$nps_prdctrl = new productView;

		//array of attributes to ignore
		$blacklist = $nps_prdctrl::getBlackListedAttributes();

		//blank array for inserting options into
		$options = array();

		//get the list of attribute that can have options selected
		$attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
		foreach ($attributes as $attribute) {
			if ($attribute->getFrontendLabel() !== '' && !empty($attribute->getFrontendLabel()) && !in_array($attribute->getId(), $blacklist)) {
				if ($value_type == 'id') {
					$attr_value = $attribute->getId();
				} elseif ($value_type == 'code') {
					$attr_value = $attribute->getAttributecode();
				} elseif ($value_type == 'both') {
					$attr_value = $attribute->getId() . $separator . $attribute->getAttributecode();
				}

				$key = strtolower(str_replace(array(' ', '_', '-'), null, $attribute->getFrontendLabel()));
				//check if key is already set
				if (!empty($options[$key])) {
					$key .= date('U');
				}
				$options[$key] = '<option value="' . $attr_value . '" ' . $this->selected($checkValue, $attr_value) . '>' . $attribute->getFrontendLabel() . '</option>';
			}
		}
		if (!empty($options)) {
			ksort($options);

		}
		return $options;
	}
}

?>


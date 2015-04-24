<?php
/**
CUSTOM DROP PHP FUNCTIONS
 */
class needPlumbingShortcodes {

	public function __construct() {
		//database read adapter
		$this->sqlread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->sqlwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		//database table prefix
		$this->tablePrefix = (string) Mage::getConfig()->getTablePrefix();

		// transfer relation
		//$select = $connection->select()->from($tablePrefix . 'catalog_product_option', array('option_id', 'in_group_id'))->where('product_id = XX AND in_group_id > 65535');

		$this->shortcode_functions = array(
			'prd_body_img' => 'productDescriptionLightboxImage',
			'prd_link' => 'productLink',
		);
	}

	public function getShotcodeLocations($content) {
		preg_match_all("|@sc|", $content, $code_start);
		preg_match_all("|@cs|", $content, $code_end);

		//set blank variables
		$count_start = 0;
		$count_end = 0;
		$find_start = "[@sc";
		$find_end = "@cs]";
		$positions = array('start' => array(), 'end' => array());

		for ($i = 0; $i < strlen($content); $i++) {
			$pos_start = strpos($content, $find_start, $count_start);
			if ($pos_start == $count_start) {
				$positions['start'][] = $pos_start;
			}
			$count_start++;

			$pos_end = strpos($content, $find_end, $count_end);
			if ($pos_end == $count_end) {
				$positions['end'][] = $pos_end;
			}
			$count_end++;
		}

		//check if there are the same number of endings as beginnings
		if (count($positions['start']) <= 0 && count($positions['start']) !== count($positions['end'])) {
			$positions = false;
		}

		return $positions;
	}

	public function getShortcodeData($locations, $content) {
		if (!is_array($locations)) {
			return false;
		}

		$shortcode_data = array();

		foreach ($locations['start'] as $key => $spos) {

			$elem = array();

			$elem['code_start'] = $spos;
			$elem['code_end'] = $locations['end'][$key] + 4;
			$elem['code_length'] = ($elem['code_end'] - $elem['code_start']);
			$elem['code_string'] = substr($content, $elem['code_start'], $elem['code_length']);

			$elem['json_start'] = $elem['code_start'] + 4;
			$elem['json_end'] = $elem['code_end'] - 4;
			$elem['json_length'] = ($elem['json_end'] - $elem['json_start']);

			$elem['json'] = substr($content, $elem['json_start'], $elem['json_length']);

			$shortcode_data[] = $elem;
		}

		return $shortcode_data;
	}

	public function processShortcodeData($shortcodes, $content, $remove = false) {

		foreach ($shortcodes as $root_key => $root_elem) {
			if ($remove) {
				$content = str_replace($root_elem['code_string'], null, $content);
			} else {
				$data_array = json_decode('{' . $root_elem['json'] . '}', TRUE);
				$replace_value = null;
				if (count($data_array) > 0) {
					foreach ($data_array as $key => $info) {
						if (!empty($this->shortcode_functions[$key])) {
							$function = $this->shortcode_functions[$key];
							$replace_value = $this->$function($info);
						}
					}
					$content = str_replace($root_elem['code_string'], $replace_value, $content);
				}
			}
		}

		return $content;
	}

	public function productDescriptionLightboxImage($data) {

		$src = 'http://images.needplumbingsupplies.com/prd_body/' . str_replace(array(' ', '-'), '_', strtolower($data['m'])) . '/' . $data['f'];
		$alt = null;
		$title = 'Click for larger view';
		$image_title = null;

		if (!empty($data['a'])) {$alt = $data['a'];}
		if (!empty($data['t'])) {
			$title = $data['t'];
			$image_title = 'title="' . $title . '" class="tooltip" ';}
		return '<a class="prd-fancybox" rel="desc-group" href="' . $src . '" title="' . $title . '"><img src="' . $src . '" alt="' . $alt . '" ' . $image_title . '></a>';
	}

	public function productLink($data) {
		if (!empty($data['id'])) {

			//set the entity ID
			$entity = $data['id'];

			//check for blank
			$blank = null;
			if (isset($data['blank'])) {
				$blank = ' target="_blank" ';
			}
			//start product model
			$productModel = Mage::getModel('catalog/product');
			$product = $productModel->load($entity);
			//set values
			$manufacturer = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
			$prd_title = $product->getName();
			$link_text = $manufacturer . ' ' . $product->getData('sku') . ' ' . $prd_title;
			$url_key = $product->getData('url_key');
			//output the value
			return '<a class="inhouse-product-link" title="Click here to view the ' . $link_text . '" href="/' . $url_key . '.html"' . $blank . '>' . $manufacturer . ' ' . $product->getData('sku') . '</a>';
		}
	}
}

?>
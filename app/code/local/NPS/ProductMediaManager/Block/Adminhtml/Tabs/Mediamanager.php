<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 * NPS Media Manager
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     NPS - Brandon Thomas <brandon@needplumbingsupplies.com>
 */

class NPS_ProductMediaManager_Block_Adminhtml_Tabs_Mediamanager extends Mage_Adminhtml_Block_Template {

	/**
	CORE FUNCTIONS
	 */
	public function __construct() {
		parent::_construct();
		$this->setTemplate('catalog/product/tab/mediamanager.phtml');
		//sql connector
		$this->resource = Mage::getSingleton('core/resource');
		$this->readConnection = $this->resource->getConnection('core_read');
		$this->writeConnection = $this->resource->getConnection('core_write');
		//run submissiont handler
		$this->_submissionHandler();
	}
	public function getProduct() {
		return Mage::registry('product');
	}
	public function isNew() {
		if ($this->getProduct()->getId()) {
			return false;
		}
		return true;
	}
	private function _submissionHandler() {
		$refresh = false;
		if (!empty($_POST['nps_function'])) {
			if ($_POST['nps_function'] == 'nps-media-manager-upload' && !empty($_FILES)) {

				//set method vars
				$image_file = $_FILES["nps-gallery-upload-input"]["tmp_name"];
				$title = $_POST['nps-gallery-image-title'];
				$in_gallery = (!empty($_POST['nps-gallery-in-gallery']) ? 1 : 0);
				$default_img = (!empty($_POST['nps-gallery-default-image']) ? 1 : 0);
				$sku = $_POST['nps-gallery-product-sku'];
				$manu = $_POST['nps-gallery-product-manu'];
				$product_id = $_POST['nps-gallery-product-id'];
				$order = $_POST['nps-media-gllery-order'];
				$image_type = $_POST['nps-media-gallery-image-type'];

				//process the uploaded image
				$this->_uploadImageHandler($image_file, $sku, $manu, $product_id, $order, $image_type, $title, $in_gallery, $default_img);
				$refresh = true;

			} elseif ($_POST['nps_function'] == 'nps-remove-gallery-image') {

				//remove image
				$this->_removeImageGalleryImage($_POST['nps-remove-image']);
				$refresh = true;
			}
		}
		//if refresh is true then reload the page to prevent duplicate posting
		if ($refresh) {
			session_write_close();
			Mage::app()->getFrontController()->getResponse()->setRedirect($_SERVER['REQUEST_URI']);
		}
	}
	/**
	SUBMISSION HANDLER CHILD FUNCTIONS
	 */
	public function _uploadImageHandler($image_file, $sku, $manu, $product_id, $order, $image_type, $title, $in_gallery, $default_img) {
		//default return value
		$return = false;

		//make sure file exists
		if (file_exists($image_file)) {

			//vefiy file is an image outputs array(width, height, version, size string, bits, mime type)
			$check = getimagesize($image_file);

			//if is image
			if ($check) {

				//set file extension if is jpeg or png
				$ext = null;
				if ($check['mime'] == 'image/png') {
					$ext = '.png';
				} elseif ($check['mime'] == 'image/jpeg') {
					$ext = '.jpeg';
				} elseif ($check['mime'] == 'image/jpg') {
					$ext = '.jpg';
				} elseif ($check['mime'] == 'image/gif') {
					$ext = '.gif';
				}

				//if file extension is set
				if (!empty($ext)) {
					//manufacturer folder
					$manu_folder = $this->convertManuToFolder($manu);
					//set the new image name
					$search_str = array(' ', '_', '#', '&', '.', '--', '(', ')');
					$replac_str = array('-', '-', '-', '-', '-', '-', null, null);

					//check the name
					$num_append = $this->_getNextImageNumber(strtolower(str_replace($search_str, $replac_str, $sku)));
					$new_image_name = strtolower(str_replace($search_str, $replac_str, $sku) . '-' . $num_append . '.jpeg');

					//$new_image_name = strtolower(str_replace($search_str, $replac_str, $sku) . '-' . $num_append . $ext);
					//set the new image path to the temp folder
					$new_image_path = '/home/image_staging/' . $manu_folder . '/';
					//set root image
					$root_img = $image_file;
					//move the image to the temp directory
					$move = move_uploaded_file($root_img, $new_image_path . $new_image_name);
					//run script
					//$ouput = shell_exec("/scripts/product_image_to_imagebase.sh " . $new_image_name . " " . $manu_folder . " 2>&1");

					//insert the record into the db as JPEG
					$this->_addImageGalleryImage(
						$product_id,
						$this->convertFileNameToJPEG($new_image_name),
						$order,
						$image_type,
						$manu,
						$title,
						$in_gallery,
						$default_img
					);
				}
			}
		}
		return $return;
	}
	private function _removeImageGalleryImage($image_id) {
		$DS = DIRECTORY_SEPARATOR;
		//get image info
		$img = $this->_getImage($image_id);
		//create the removal file
		$remove_file = Mage::getBaseDir() . $DS . 'var' . $DS . 'img_tmp' . $DS . $image_id . '.txt';
		$fileHandle = fopen($remove_file, "w+");
		//set raw delete
		$raw_delete = 'rm -f /home/img_usr/catalog/product/' . $img['manu'];
		//size folders
		$size_folders = array('65x65', '75x75', '80x80', '100x100', '185x185', '200x200', '250x250', '300x300', 'x1200', '1800x', 'full');
		//set base output
		$output = array();
		foreach ($size_folders as $size) {
			$output[] = $raw_delete . "/" . $size . "/" . $img['file_name'] . "\n";
		}
		//if there is content for the file write it
		if (!empty($output)) {
			$output = implode("\n", $output);
			fwrite($fileHandle, $output);
		}
		//close the file connection
		fclose($fileHandle);
		//run shell command to send file to image base
		shell_exec("/scripts/remove_images_from_imagebase.sh " . $image_id . ".txt 2>&1");
		//sttempt to remove the file (protects from running on local)
		$this->_removeDBImage($image_id);
		/*if (unlink($remove_file)) {
	//remove database record
	$this->_removeDBImage($image_id);
	}*/
	}
	/**
	LOGGING FUNCTIONS
	 */
	private function _imageLog($data) {
		$DS = DIRECTORY_SEPARATOR;
		//check file size is larger than 1mb and create a new one if so
		if (filesize(Mage::getBaseDir() . $DS . "image_upload.txt") > 1024) {
			rename(Mage::getBaseDir() . $DS . "image_upload.txt", Mage::getBaseDir() . $DS . "image_upload." . date('U') . ".txt");
		}
		//start output buffer
		ob_start();
		if (is_array($data)) {
			var_dump($data);
		} else {
			echo $data;
		}
		$output = ob_get_clean();
		$fileHandle = fopen(Mage::getBaseDir() . $DS . "image_upload.txt", "a+");
		fwrite($fileHandle, $output);
		fclose($fileHandle);
	}
	/**
	STRING FUNCTIONS
	 */
	private function convertFileNameToJPEG($filename) {
		return substr($filename, 0, strripos($filename, '.')) . '.jpeg';
	}
	public function convertManuToFolder($manu) {
		return strtolower(str_replace(array(' ', '-', '_'), null, $manu));
	}
	/**
	DATABASE FUNCTIONS
	 */
	public function _removeDBImage($image_id) {
		$query = "DELETE FROM `nps_product_media_gallery` WHERE `id` = " . $image_id;
		$this->readConnection->query($query);
	}
	public function _getImage($image_id) {
		$query = "SELECT `id`,`product_id`,`manu`,`file_name`,`order`, `type`, `title`, `in_gallery`, `default_img` FROM `nps_product_media_gallery` WHERE `id` = " . $image_id;
		$this->readConnection->query($query);
		$results = $this->readConnection->fetchRow($query);
		return $results;
	}
	public function _getImages($product_id) {
		$query = "SELECT `id`,`product_id`,`manu`,`file_name`,`order`, `type`, `title`, `in_gallery`, `default_img` FROM `nps_product_media_gallery` WHERE `product_id` = " . $product_id . " ORDER BY `order`";
		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}
	public function _getMageImage($product_id) {
		$query = "SELECT `product_id`,`image`,`sku`,`manufacturer`,`is_primary` FROM `mage_images_plus_sku_manu` WHERE `product_id` = " . $product_id;
		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}
	public function _getFinishImages($product_id) {
		$query = "SELECT `product_id`,`image`,`sku`,`manufacturer`,`is_primary` FROM `mage_images_plus_sku_manu` WHERE `product_id` = " . $product_id;
		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;
	}
	public function _getChildGalleryImages($product_id) {

		$query = " SELECT ";

		$query .= "	`custom_options_relation`.`product_id`, ";
		$query .= " `custom_options_relation`.`option_id`, ";
		$query .= " `catalog_product_option_type_value`.`option_type_id`, ";
		$query .= " `catalog_product_option_type_value`.`sku`, ";
		$query .= " `catalog_product_option_type_value`.`sort_order`, ";
		$query .= " `catalog_product_entity`.`entity_id` AS `child_id`, ";
		$query .= " `nps_product_media_gallery`.`id`, ";
		$query .= " `nps_product_media_gallery`.`manu`, ";
		$query .= " `nps_product_media_gallery`.`file_name`, ";
		$query .= " `nps_product_media_gallery`.`order`, ";
		$query .= " `nps_product_media_gallery`.`type`, ";
		$query .= " `nps_product_media_gallery`.`title`, ";
		$query .= " `nps_product_media_gallery`.`in_gallery`, ";
		$query .= " `nps_product_media_gallery`.`default_img` ";

		$query .= " FROM `custom_options_relation` ";
		$query .= " INNER JOIN ";
		$query .= " 	`catalog_product_option_type_value` ON `catalog_product_option_type_value`.`option_id` = `custom_options_relation`.`option_id` ";
		$query .= " INNER JOIN  ";
		$query .= " 	`catalog_product_entity` ON `catalog_product_entity`.`sku` = `catalog_product_option_type_value`.`sku` ";
		$query .= " LEFT JOIN  ";
		$query .= " 	`nps_product_media_gallery` ON `nps_product_media_gallery`.`product_id` = `catalog_product_entity`.`entity_id` ";
		$query .= " WHERE `custom_options_relation`.`product_id` = " . $product_id;

		$this->readConnection->query($query);
		$results = $this->readConnection->fetchAll($query);
		return $results;

	}
	public function _getNextImageNumber($converted_sku) {
		$query = "SELECT MAX(REPLACE(REPLACE(`file_name`,'" . $converted_sku . "-',''),'.jpeg','')) + 1 as `nums` FROM `nps_product_media_gallery` WHERE `file_name` like '%" . $converted_sku . "%'";
		$results = $this->readConnection->fetchRow($query);
		if ($results) {
			return $results['nums'];
		} else {
			return 1;
		}

	}
	public function _addImageGalleryImage($product_id, $file, $order, $type, $manu, $title, $in_gallery, $default_img) {

		//verify order is available
		$existing = $this->_getImages($product_id);
		foreach ($existing as $eimg) {
			if ($order = $eimg['order']) {
				$order++;
			}
		}
		$manu_folder = $this->convertManuToFolder($manu);
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$connection->beginTransaction();
		$__fields = array();
		$__fields['product_id'] = $product_id;
		$__fields['file_name'] = $file;
		$__fields['order'] = $order;
		$__fields['type'] = $type;
		$__fields['manu'] = $manu_folder;
		$__fields['title'] = $title;
		$__fields['in_gallery'] = $in_gallery;
		$__fields['default_img'] = $default_img;
		$connection->insert('nps_product_media_gallery', $__fields);
		$connection->commit();
	}
	public function _reorderImage($image_id, $product_id, $old_order, $new_order) {
		if ($old_order > $new_order) {
			$query = "UPDATE `nps_product_media_gallery` SET `order` = `order` + 1 WHERE `product_id` = '" . $product_id . "' AND `order` BETWEEN " . $new_order . " AND " . $old . " AND `id` <> " . $image_id;
		} else {
			$query = "UPDATE `nps_product_media_gallery` SET `order` = `order` - 1 WHERE `product_id` = '" . $product_id . "' AND `order` BETWEEN " . $new_order . " AND " . $old . " AND `id` <> " . $image_id;
		}
		$this->writeConnection->query($query);
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
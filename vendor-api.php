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
 *
 * @category    Mage
 * @package     NPS Custom Vendor Api
 * @copyright   Copyright (c) 2015 Need Plumbing Supplies. (http://www.needplumbingsupplies.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

?>
<?php
//verify that secret is set and matches database
if (isset($_GET['vendor']) && isset($_GET['function']) && isset($_GET['secret'])) {

	//set error reporting
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);

	//set magento vore application
	$mageFilename = getcwd() . '/app/Mage.php';
	if (!file_exists($mageFilename)) {
		echo 'Mage file not found';
		exit;
	}
	require $mageFilename;
	Mage::register('custom_entry_point', true);
	Mage::$headersSentThrowsException = false;
	Mage::init('admin');
	Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
	Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_EVENTS);

	//check database to verify secret match
	$connection_read = Mage::getSingleton('core/resource')->getConnection('core_read');
	$connection_read = Mage::getSingleton('core/resource')->getConnection('core_write');

	//check user
	$query = "SELECT * FROM nps_dev.api_user WHERE username = '" . $_GET['vendor'] . "'";
	$connection_read->query($query);
	$vendor_user = $connection_read->fetchRow($query);
	$secret_decrypted = Mage::getModel('core/encryption')->decrypt($vendor_user['api_key']);

	var_dump($_REQUEST);
	var_dump($vendor_user);
	var_dump($secret_decrypted);
}

?>
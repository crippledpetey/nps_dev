<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerLocation
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Customer GeoIP Location extension
 * Exception class
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerLocation
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */
class MageWorx_CustomerLocation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_ENABLED_IN_ORDERS = 'mageworx_geoip/geolocation/enable_orders';
    const XML_ENABLED_IN_ONLINE_CUSTOMERS = 'mageworx_geoip/geolocation/enable_online_customers';

    /**
     * Checks if extension enabled for order view
     *
     * @return bool
     */
    public function isEnabledForOrders()
    {
        return Mage::getStoreConfigFlag(self::XML_ENABLED_IN_ORDERS);
    }

    /**
     * Checks if extension enabled for "online customers" grid
     *
     * @return bool
     */
    public function isEnabledForCustomers()
    {
        return Mage::getStoreConfigFlag(self::XML_ENABLED_IN_ONLINE_CUSTOMERS);
    }

    /**
     * Returns location html
     *
     * @param mixed $obj
     * @return mixed
     */
    public function getGeoIpHtml($obj)
    {
        $block = Mage::app()->getLayout()
            ->createBlock('core/template')
            ->setTemplate('mageworx/geolocation/adminhtml-customer-geoip.phtml')
            ->addData(array('item' => $obj))
            ->toHtml();

        return $block;
    }
}
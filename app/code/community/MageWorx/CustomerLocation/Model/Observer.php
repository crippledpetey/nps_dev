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

class MageWorx_CustomerLocation_Model_Observer
{
    /**
     * Adds GeoIP location html to order view
     *
     * @param Varien_Event_Observer $observer
     * @return MageWorx_CustomerLocation_Model_Observer
     */
    public function orderLocation(Varien_Event_Observer $observer)
    {
        if(Mage::helper('mageworx_geolocation')->isEnabledForOrders()){
            $_order = null;
            $block = $observer->getEvent()->getBlock();
            $controller = Mage::app()->getRequest()->getControllerName();
            if($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Info && $controller == 'sales_order'){
                $_order = $block->getOrder();
            } else if($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form && $controller == 'sales_order_shipment'){
                $_order = $block->getShipment()->getOrder();
            } else if($block instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_View_Form && $controller == 'sales_order_invoice'){
                $_order = $block->getInvoice()->getOrder();
            }

            if(!is_null($_order)) {
                $ip = $_order->getRemoteIp();
                if (!$ip) {
                    return $this;
                }

                $geoIpObj = Mage::getModel('mageworx_geoip/geoip')->getLocation($ip);

                if ($geoIpObj->getCode()) {
                    $obj = new Varien_Object();
                    $obj->addData(array(
                        'geo_ip' => $geoIpObj,
                        'ip' => $ip,
                    ));
                    $block->getOrder()->setRemoteIp(Mage::helper('mageworx_geolocation')->getGeoIpHtml($obj));
                }
            }
        }
        return $this;
    }

    /**
     * Adds GeoIP location to new column in "online customers grid"
     *
     * @param Varien_Event_Observer $observer
     * @return MageWorx_CustomerLocation_Model_Observer
     */
    public function onlineCustomerLocation(Varien_Event_Observer $observer)
    {
        if (!($block = $observer->getEvent()->getBlock()) || !($block instanceof Mage_Adminhtml_Block_Customer_Online_Grid)) {
            return $this;
        }

        if (!Mage::helper('mageworx_geolocation')->isEnabledForCustomers()) {
            return $this;
        }

        $block->addColumnAfter('geoip', array(
            'header'    => Mage::helper('mageworx_geoip')->__('IP Location'),
            'index'     => 'remote_addr',
            'align'     => 'left',
            'width'     => 200,
            'renderer'  => 'mageworx_geolocation/adminhtml_customer_online_grid_renderer_geoip',
            'filter'    => false,
            'sortable'  => false,
        ), 'ip_address');
    }
}
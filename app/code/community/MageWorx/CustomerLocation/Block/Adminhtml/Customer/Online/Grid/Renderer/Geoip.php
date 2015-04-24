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

class MageWorx_CustomerLocation_Block_Adminhtml_Customer_Online_Grid_Renderer_Geoip extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders GeoIP Location column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $ip = long2ip($row->getData($this->getColumn()->getIndex()));
        $row->setData('geo_ip', Mage::getSingleton('mageworx_geoip/geoip')->getLocation($ip));

        return Mage::helper('mageworx_geolocation')->getGeoIpHtml($row);
    }
}
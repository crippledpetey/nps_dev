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
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * GeoIP extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_GeoIP_Model_Observer
{
    /**
     * Updates database with cron
     *
     * @param mixed $schedule
     * @throws Exception
     */
    public function cronUpdateDatabase($schedule)
    {
        $helper = Mage::helper('mageworx_geoip');
        $geoip = Mage::getModel('mageworx_geoip/geoip');

        $geoip->downloadFile($helper->getDbUpdateSource(), $helper->getTempUpdateFile());

        if(file_exists($helper->getDatabasePath())){
            copy($helper->getDatabasePath(), $helper->getDatabasePath() . '_backup_' . time());
        }

        $geoip->uncompressFile($helper->getTempUpdateFile(), $helper->getDatabasePath());

        unlink($helper->getTempUpdateFile());

        Mage::getModel('core/config')->saveConfig(MageWorx_GeoIP_Helper_Data::XML_GEOIP_UPDATE_DB, time());

        return true;
    }

    public function changeDbTypeAfter($observer)
    {
        if($observer->getObject()->getSection() != 'mageworx_customers'){
            return $this;
        }
        $helper = Mage::helper('mageworx_geoip');

        if(file_exists($helper->getDatabasePath())){
            return $this;
        }

        $geoip = Mage::getModel('mageworx_geoip/geoip');
        $errors = $geoip->downloadFile($helper->getDbUpdateSource(), $helper->getTempUpdateFile());
        if(!empty($errors)){
            $errors = implode(', ', $errors);
            throw new Exception($errors);
        }
        $success = Mage::getModel('mageworx_geoip/geoip')->uncompressFile($helper->getTempUpdateFile(), $helper->getDatabasePath());
        if(!$success){
            throw new Exception($helper->__('Cannot extract database'));
        }
        unlink($helper->getTempUpdateFile());

        return $this;
    }
}
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

class MageWorx_GeoIP_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_GEOIP_DATABASE_TYPE   = 'mageworx_geoip/geoip/db_type';
    const XML_GEOIP_UPDATE_DB       = 'mageworx_geoip/geoip/update_db';

    const DB_COUNTRY_UPDATE_SOURCE  = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz';
    const DB_CITY_UPDATE_SOURCE     = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz';

    /**
     * Gets DB Type to be used
     *
     * @return bool
     */
    public function isCityDbType()
    {
        if(Mage::app()->getStore()->isAdmin()){
            $groups = Mage::app()->getRequest()->getParam('groups', false);
            if($groups && !empty($groups['geoip']['fields']['db_type']['value'])){
                return $groups['geoip']['fields']['db_type']['value'] == 2;

            }
        }

        return (Mage::getStoreConfig(self::XML_GEOIP_DATABASE_TYPE) == MageWorx_GeoIP_Model_Source_Dbtype::GEOIP_CITY_DATABASE);
    }

    /**
     * Returns full path to GeoIP database
     *
     * @return string
     */
    public function getDatabasePath()
    {
        $cityPath = Mage::getBaseDir() . DS . 'lib' . DS . 'GeoIP' . DS . 'GeoLiteCity.dat';
        $countryPath = Mage::getBaseDir() . DS . 'lib' . DS . 'GeoIP' . DS . 'GeoIP.dat' ;

        if ($this->isCityDbType()){
            return $cityPath;
        } else {
            return $countryPath;
        }

        return $path;
    }

    /**
     * Returns source for database update
     *
     * @return string
     */
    public function getDbUpdateSource()
    {
        if ($this->isCityDbType()) {
            return self::DB_CITY_UPDATE_SOURCE;
        } else {
            return self::DB_COUNTRY_UPDATE_SOURCE;
        }
    }

    public function getTempUpdateFile()
    {
        $dbPath = $this->getDatabasePath();
        return $dbPath . '_temp.gz';
    }

    /**
     * Changes country code to upper case
     *
     * @param string $countryCode
     * @return string
     */
    public function prepareCode($countryCode)
    {
        return strtoupper(trim($countryCode));
    }

    /**
     * Gets customer ip
     *
     * @return string
     */
    public static function getCustomerIp()
    {
        //return '24.24.24.24'; //USA
        //return '62.147.0.1'; // FRANCE
        //return '81.13.146.205'; //Swiss
        //return '78.159.112.71'; //Germany
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $ipArr = explode(',', $ip);
        $ip = $ipArr[count($ipArr) - 1];

        return trim($ip);
    }

    /**
     * Sets encoded cookie
     *
     * @param string $key
     * @param mixed $value
     * @return Zend_Controller_Request_Http
     */
    public function setCookie($key, $value, $encode = true)
    {
        $cookie = Mage::getModel('core/cookie');

        foreach(Mage::app()->getStores() as $store){
            $urlParse = parse_url($store->getBaseUrl());
            $path = rtrim(str_replace('index.php', '', $urlParse['path']), '/');
            if (!empty($path)) {
                $cookie->set($key, $value, true, $path);
            }
        }

        $cookie->set($key, $value, true, '/');

        return true;
    }

    /**
     * Returns decoded cookie
     *
     * @param string $key
     * @return bool|string
     */
    public function getCookie($key, $decode = false)
    {
        $cookie = Mage::getModel('core/cookie');
        if ($cookie->get($key)) {
            $result = $cookie->get($key);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Returns path to country flag
     *
     * @param string $name
     * @return string
     */
    public function getFlagPath($name = null)
    {
        $flagName = strtolower($name) . '.png';
        $filePath = Mage::getSingleton('core/design_package')->getSkinBaseUrl(array('_area' => 'adminhtml')) . DS . 'images' . DS . 'mageworx' . DS . 'flags' . DS . $flagName;

        if (!file_exists($filePath)) {
            return Mage::getDesign()->getSkinUrl('images' . DS . 'mageworx' . DS . 'flags' . DS . $flagName);
        } else {
            return $filePath;
        }
    }

    /**
     * Get time of last DB update
     *
     * @return string
     */
    public function getLastUpdateTime()
    {
        $time = Mage::getStoreConfig(self::XML_GEOIP_UPDATE_DB);
        if(!$time){
            return false;
        }
        return date('F d, Y / h:i', $time);
    }

    /**
     * Check whether country code is valid
     *
     * @param $code
     * @return bool
     */
    public function checkCountryCode($code)
    {
        $allCountries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(true);
        $code = $this->prepareCode($code);

        $isValid = false;
        foreach ($allCountries as $country) {
            if ($country['value'] == $code) {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }
}
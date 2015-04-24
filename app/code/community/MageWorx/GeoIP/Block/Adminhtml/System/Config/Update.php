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

class MageWorx_GeoIP_Block_Adminhtml_System_Config_Update extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Adds update button to config field
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->_getAddRowButtonHtml();
    }

    /**
     * Returns update button html
     *
     * @param string $sku
     * @return mixed
     */
    protected function _getAddRowButtonHtml()
    {
        $url = Mage::helper('adminhtml')->getUrl('mageworx/adminhtml_geoip_database/update/');
        $buttonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($this->__('Update Database'))
            ->setOnClick("startUpdate()")
            ->toHtml();

        $backupHtml = 'Create backup <input type="checkbox" id="mwgeoip_update_backup" name="backup" value="1" checked="checked">&nbsp;&nbsp;&nbsp;';
        $js = '<script type="text/javascript">
        function startUpdate(){
            url = "' . $url . '";
            backup = $("mwgeoip_update_backup");
            if(backup.checked){
                url = url.replace("/update/", "/update/backup/1/");
            }
            window.open(url);
        }
        </script>';

        $lastUpdate = "<br>" . Mage::helper('mageworx_geoip')->__('Last update') . ": ";
        if (Mage::helper('mageworx_geoip')->getLastUpdateTime()) {
            $lastUpdate .= Mage::helper('mageworx_geoip')->getLastUpdateTime();
        } else {
            $lastUpdate .= 'n/a';
        }

        return $backupHtml . $buttonHtml . $js . $lastUpdate;
    }

}
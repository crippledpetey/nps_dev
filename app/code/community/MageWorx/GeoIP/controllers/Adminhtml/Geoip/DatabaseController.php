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

class MageWorx_GeoIP_Adminhtml_Geoip_DatabaseController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Start update action
     */
    public function updateAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Updates database step-by-step
     */
    public function runUpdateAction()
    {
        $helper = Mage::helper('mageworx_geoip');
        $action = $this->getRequest()->getParam('action', false);
        $nextStep = false;
        $success = true;
        $errors = array();

        switch ($action) {
            case 'start':
                $result['text'] = $this->__('Downloading database archive...');
                $nextStep = 'download';
                break;

            case 'download':
                $errors = Mage::getModel('mageworx_geoip/geoip')->downloadFile($helper->getDbUpdateSource(), $helper->getTempUpdateFile());
                if(count($errors)){
                    $success = false;
                }
                if ($this->getRequest()->getParam('backup') && file_exists($helper->getDatabasePath())) {
                    $result['text'] = $this->__('Creating current database backup...');
                    $nextStep = 'backup';
                } else {
                    $result['text'] = $this->__('Uncompressing archive...');
                    $nextStep = 'unpack';
                }
                break;

            case 'backup':
                $success = copy($helper->getDatabasePath(), $helper->getDatabasePath() . '_backup_' . time());
                $result['text'] = $this->__('Uncompressing archive...');
                $nextStep = 'unpack';
                break;

            case 'unpack':
                $success = Mage::getModel('mageworx_geoip/geoip')->uncompressFile($helper->getTempUpdateFile(), $helper->getDatabasePath());
                $result['text'] = $this->__('Deleting temporary files...');
                $nextStep = 'delete';
                break;

            case 'delete':
                unlink($helper->getTempUpdateFile());

                Mage::getModel('core/config')->saveConfig(MageWorx_GeoIP_Helper_Data::XML_GEOIP_UPDATE_DB, time());

                $result['stop'] = true;
                $result['url']  = '';
                break;
        }

        if ($nextStep) {
            $result['url'] = $this->getUrl('*/*/runUpdate/', array('action' => $nextStep, 'backup' => $this->getRequest()->getParam('backup', 0)));
        }
        if (!$success) {
            $result['error'] = true;
            if (count($errors)) {
                $result['text'] = implode('<br>', $errors);
            } else {
                $result['text']  = $this->__('An error occured while updating GeoIP database.');
            }
            $result['stop']  = true;
            $result['url']   = '';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
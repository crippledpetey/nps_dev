<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   1.0.9
 * @build     742
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Rma_Helper_Mail
{
	public $emails = array();
	protected function getConfig() {
		return Mage::getSingleton('rma/config');
	}

	protected function getSender() {
		return $this->getConfig()->getNotificationSenderEmail();
	}

    protected function send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId, $code, $attachments) {
		if (!$senderEmail || !$recipientEmail || $templateName == 'none') {
			return false;
		}

        // save current design settings
        $currentDesignConfig = clone $this->_getDesignConfig();

        $this->_setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $this->_applyDesignConfig();

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $isActiveHelpdesk = $this->getConfig()->isActiveHelpdesk();
        $variables = array_merge($variables, array(
            'logo_url' => $this->_getLogoUrl($storeId),
            'logo_alt' => $this->_getLogoAlt($storeId),
            'hidden_separator' => $isActiveHelpdesk? Mage::helper('helpdesk/email')->getHiddenSeparator(): '',
            'hidden_code' => $isActiveHelpdesk? Mage::helper('helpdesk/email')->getHiddenCode($code): '',
        ));

        $template = Mage::getModel('core/email_template');
        foreach($attachments as $attachment) {
            $template->getMail()->createAttachment($attachment->getBody(), $attachment->getType())->filename = $attachment->getName();
        }

        $template->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                 ->sendTransactional($templateName,
                 array(
                     'name' => $senderName,
                     'email' => $senderEmail,
                 ),
                 $recipientEmail, $recipientName, $variables);

		$text = $template->getProcessedTemplate($variables, true);

		$this->emails[]= array('text'=>$text, 'recipient_email'=>$recipientEmail, 'recipient_name'=>$recipientName);
        $translate->setTranslateInline(true);
        // restore previous design settings
        $this->_setDesignConfig($currentDesignConfig->getData());
        $this->_applyDesignConfig();
		return true;
    }

    public function sendNotificationCustomerEmail($rma, $comment)
    {
        $attachments = array();
        if (is_object($comment)) {
            $attachments = $comment->getAttachments();
            $comment = $comment->getTextHtml();
        }
    	$templateName = $this->getConfig()->getNotificationCustomerEmailTemplate();

    	$recipientEmail = $rma->getEmail();
    	$recipientName = $rma->getName();
		$variables = array(
            'customer' => $rma->getCustomer(),
            'rma' => $rma,
			'comment' => $comment,
            'store' => $rma->getStore(),
        );

		$senderName = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/name");
		$senderEmail = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/email");

		$this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $rma->getStore()->getId(), $rma->getCode(), $attachments);
    }

    public function sendNotificationAdminEmail($rma, $comment)
    {
        $attachments = array();
        if (is_object($comment)) {
            $attachments = $comment->getAttachments();
            $comment = $comment->getTextHtml();
        }
    	$templateName = $this->getConfig()->getNotificationAdminEmailTemplate();
        if ($user = $rma->getUser()) {
            $recipientEmail = $user->getEmail();
        } else {
            return;
        }

    	$recipientName = '';

        $variables = array(
            'customer' => $rma->getCustomer(),
            'rma' => $rma,
            'comment' => $comment,
            'store' => $rma->getStore(),
        );
		$senderName = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/name");
		$senderEmail = Mage::getStoreConfig("trans_email/ident_{$this->getSender()}/email");
		$this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $rma->getStore()->getId(), $rma->getCode(), $attachments);
    }

    /**
     * Can parse template and return ready text
     * @param  string $variable  - text with variables like {{var customer.name}}
     * @param  array $variables - array of variables
     * @param  array $storeId
     * @return string            - ready text
     */
    public function processVariable($variable, $variables, $storeId)
    {
        // save current design settings
        $currentDesignConfig = clone $this->_getDesignConfig();

        $this->_setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
        $this->_applyDesignConfig();
        $template = Mage::getModel('core/email_template');
        $template->setTemplateText($variable);
        $html = $template->getProcessedTemplate($variables);
        // restore previous design settings
        $this->_setDesignConfig($currentDesignConfig->getData());
        $this->_applyDesignConfig();
        return $html;
    }

    protected function _getLogoUrl($store)
    {
        $store = Mage::app()->getStore($store);
        $fileName = $store->getConfig('design/email/logo');
        if ($fileName) {
            $uploadDir = Mage_Adminhtml_Model_System_Config_Backend_Email_Logo::UPLOAD_DIR;
            $fullFileName = Mage::getBaseDir('media') . DS . $uploadDir . DS . $fileName;
            if (file_exists($fullFileName)) {
                return Mage::getBaseUrl('media') . $uploadDir . '/' . $fileName;
            }
        }
        return Mage::getDesign()->getSkinUrl('images/logo_email.gif');
    }


    protected function _getLogoAlt($store)
    {
        $store = Mage::app()->getStore($store);
        $alt = $store->getConfig('design/email/logo_alt');
        if ($alt) {
            return $alt;
        }
        return $store->getFrontendName();
    }


    protected $_designConfig;
    protected function _setDesignConfig(array $config)
    {
        $this->_getDesignConfig()->setData($config);
        return $this;
    }

    protected function _getDesignConfig()
    {
        if(is_null($this->_designConfig)) {

            $store = is_object(Mage::getDesign()->getStore())
                ? Mage::getDesign()->getStore()->getId()
                : Mage::getDesign()->getStore();

            $this->_designConfig = new Varien_Object(array(
                'area' => Mage::getDesign()->getArea(),
                'store' => $store,
                'theme' => Mage::getDesign()->getTheme('template'),
                'package_name' => Mage::getDesign()->getPackageName(),
            ));
        }
        return $this->_designConfig;
    }

    protected function _applyDesignConfig()
    {
        $designConfig = $this->_getDesignConfig();
        $design = Mage::getDesign();
        $designConfig->setOldArea($design->getArea())
            ->setOldStore($design->getStore());

        if ($designConfig->hasData('area')) {
            Mage::getDesign()->setArea($designConfig->getArea());
        }
        if ($designConfig->hasData('store')) {
            $store = $designConfig->getStore();
            Mage::app()->setCurrentStore($store);

            $locale = new Zend_Locale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $store));
            Mage::app()->getLocale()->setLocale($locale);
            Mage::app()->getLocale()->setLocaleCode($locale->toString());
            if ($designConfig->hasData('area')) {
                Mage::getSingleton('core/translate')->setLocale($locale)
                    ->init($designConfig->getArea(), true);
            }
            $design->setStore($store);
            $design->setTheme('');
            $design->setPackageName('');
        }
        if ($designConfig->hasData('theme')) {
            Mage::getDesign()->setTheme($designConfig->getTheme());
        }
        if ($designConfig->hasData('package_name')) {
            Mage::getDesign()->setPackageName($designConfig->getPackageName());
        }
        return $this;
    }

    /************************/

    public function parseVariables($text, $rma) {
        $variables = array(
            'rma' => $rma,
            'order' => $rma->getOrder(),
            'status' => $rma->getStatus(),
            'customer' => $rma->getCustomer(),
            'store' => $rma->getStore(),
        );
        $text = $this->processVariable($text, $variables, $rma->getStoreId());
        return $text;
    }

}
<?php

/**
 * eBizCharge Magento Plugin.
 * v1.0.4 - March 6th, 2013
 *
 * For assistance please contact support@ebizcharge.com
 *
 * Copyright (c) 2013 Century Business Solutions
 * All rights reserved.
 *
 *
 * @category    Mage
 * @package     Mage_Ebizcharge_Block_Form
 * @copyright   Copyright (c) 2013 Century Business Solutions  (www.centurybizsolutions.com)
 */
class Mage_Ebizcharge_Block_Form extends Mage_Payment_Block_Form {

    protected $token;  //eBizCharge Customer ID returned from gateway
    protected $sourceKey;  //source key entered in admin configuation
    protected $pin;  //source key pin entered in admin configuration
    protected $sandbox;  //is sandbox mode enabled

    protected function _construct() {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'));
        $token = Mage::getModel('ebizcharge/token')->getCollection()
                ->addFieldToFilter('mage_cust_id', $session->getCustomer()->getId())
                ->getFirstItem();
        $this->token = $token;

        $this->sourceKey = Mage::getStoreConfig('payment/ebizcharge/sourcekey');
        $this->pin = Mage::getStoreConfig('payment/ebizcharge/sourcepin');
        $this->sandbox = Mage::getStoreConfig('payment/ebizcharge/sandbox') ? TRUE : FALSE;

        $this->setTemplate('ebizcharge/form.phtml');
        parent::_construct();
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig() {
        return Mage::getSingleton('payment/config');
    }

    public function getCcAvailableTypes() {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code => $name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    public function getCcMonths() {
        $months['01'] = 'January';
        $months['02'] = 'February';
        $months['03'] = 'March';
        $months['04'] = 'April';
        $months['05'] = 'May';
        $months['06'] = 'June';
        $months['07'] = 'July';
        $months['08'] = 'August';
        $months['09'] = 'September';
        $months['10'] = 'October';
        $months['11'] = 'November';
        $months['12'] = 'December';
        return $months;
    }

    public function getCcYears() {
        for ($i = 0; $i <= 10; $i++)
            $years[date('Y', strtotime("+$i years"))] = date('Y', strtotime("+$i years"));
        return $years;
    }

    /**
     * Check whether an eBizCharge customer ID is
     * associated with current magento customer ID
     *
     * @return boolean
     */
    protected function hasToken() {
        $data = $this->token->getData();
        return empty($data) ? FALSE : TRUE;
    }

    /**
     * Get list of payment methods saved on gateway
     *
     * @return object Payment methods saved on gateway
     */
    protected function getPaymentMethods() {
        $wsdl = $this->_getWsdlUrl();
        $client = new SoapClient($wsdl);
        try {
//            throw new SoapFault('custom error', 'error msg');
//            Mage::getSingleton('core/session')->addError('getter:' . $this->token->getEbzcCustId());
            $methods = $client->getCustomerPaymentMethods(
                    $this->_getUeSecurityToken(), $this->token->getEbzcCustId()
            );
            return $methods;
        } catch (SoapFault $ex) {
            Mage::getSingleton('core/session')->addError($this->__($this->escapeHtml(Mage::getStoreConfig('payment/ebizcharge/error_msg'))));
            Mage::getSingleton('core/session')->addError($ex->getMessage());
            Mage::throwException($ex->getMessage());
        }
    }

    protected function setEbzcCustId($ebzc_cust_id) {
//        Mage::getSingleton('core/session')->addError('setter:' . $ebzc_cust_id);
        $this->token->setEbzcCustId($ebzc_cust_id); // = $token;
    }

    protected function updateEbzcCustId($customerId) {
        $token = Mage::getModel('ebizcharge/token')->getCollection()
                ->addFieldToFilter('mage_cust_id', $customerId)
                ->getFirstItem();
        $this->setEbzcCustId($token->getEbzcCustId());
    }

    /**
     * Return eBizCharge customer ID
     * associated with current magento customer ID
     *
     * @return int eBizCharge Customer ID
     */
    protected function getEbzcCustId() {
        return $this->token->getEbzcCustId();
    }

    /**
     * Returns the WSDL URL based on sandbox mode
     *
     * @return string WSDL URL for SOAP call
     */
    protected function _getWsdlUrl() {
        return $this->sandbox ?
                'https://sandbox.ebizcharge.com/soap/gate/246CEB5A/ebizcharge.wsdl' :
                'https://secure.ebizcharge.com/soap/gate/246CEB5A/ebizcharge.wsdl';
    }

    /**
     * Returns the ueSecurityToken
     *
     * @return array ueSucurityToken for SOAP calls
     */
    protected function _getUeSecurityToken() {
        if ($this->pin) {
            $seed = time() . rand();
            $clear = $this->sourceKey . $seed . $this->pin;
            $hash = sha1($clear);
        }
        $token = array();
        $token['SourceKey'] = $this->sourceKey;
        if ($hash) {
            $token['PinHash'] = array(
                'Type' => 'sha1',
                'Seed' => $seed,
                'HashValue' => $hash
            );
        }
        $token['ClientIP'] = Mage::helper('core/http')->getRemoteAddr();
        return $token;
    }

}

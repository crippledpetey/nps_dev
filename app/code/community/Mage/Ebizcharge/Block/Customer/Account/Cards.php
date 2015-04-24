<?php

class Mage_Ebizcharge_Block_Customer_Account_Cards extends Mage_Core_Block_Template {

    protected $_mage_cust_id;
    protected $_ebzc_cust_id;
    protected $_tran;

    public function __construct() {
        parent::__construct();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $this->_mage_cust_id = $customer->getId();
        $this->_ebzc_cust_id = Mage::getModel('ebizcharge/token')->getCollection()
                ->addFieldToFilter('mage_cust_id', $customer->getId())
                ->getFirstItem()
                ->getEbzcCustId()
        ;
        $this->_tran = Mage::getModel('ebizcharge/TranApi');
        if (Mage::getStoreConfig('payment/ebizcharge/sandbox')) {
            $this->_tran->usesandbox = true;
        }
        $this->_tran->key = Mage::getStoreConfig('payment/ebizcharge/sourcekey');
        $this->_tran->pin = Mage::getStoreConfig('payment/ebizcharge/sourcepin');
        $this->_tran->software = 'Mage_Ebizcharge 1.0.2';
    }

    public function getEbzcCustId() {
        return $this->_ebzc_cust_id;
    }

    public function getMageCustId() {
        return $this->_mage_cust_id;
    }

    public function getEbzcMethodId() {
        $mid = Mage::app()->getFrontController()->getRequest()->getParam('mid');
        return $mid;
    }

    public function getMethodName() {
        $method = Mage::app()->getFrontController()->getRequest()->getParam('method');
        return urldecode($method);
    }

    public function getPaymentMethods() {
        $wsdl = $this->_tran->_getWsdlUrl();
        $ueSecurityToken = $this->_tran->_getUeSecurityToken();
        $client = new SoapClient($wsdl);
        try {
            $paymentMethods = $client->getCustomerPaymentMethods($ueSecurityToken, $this->_ebzc_cust_id);
            return $paymentMethods;
        } catch (Exception $ex) {
            return NULL;
        }
    }

    public function getBackUrl() {
        return $this->getUrl('customer/account/');
    }

    public function getAddCardUrl() {
        return $this->getUrl('ebizcharge/index/addCard/');
    }

}

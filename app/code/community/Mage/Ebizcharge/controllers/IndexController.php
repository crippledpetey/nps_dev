<?php

class Mage_Ebizcharge_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function addCardAction() {
        $this->loadLayout();
        $navigation = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigation) {
            $navigation->setActive('ebizcharge/index/');
        }
        $this->renderLayout();
    }

    public function addCardPostAction() {
        if ($this->getRequest()->isPost()) {
            $billing = $this->getRequest()->getPost('billing');
            $billing['country'] = Mage::getModel('directory/country')->load($this->getRequest()->getParam('country_id'))->getName();
            if ($billing['region_id']) {
                $billing['region'] = Mage::getModel('directory/region')->load($billing['region_id'])->getName();
            }
            $payment = $this->getRequest()->getPost('payment');
            $default = $payment['default'] ? true : false;
            $paymentMethod = array(
                'MethodName' => $payment['cc_type'] . ' ' . substr($payment['cc_number'], -4)
                . ' - ' . $payment['cc_holder'],
                'SecondarySort' => $default ? 0 : 1,
//                'SecondarySort' => 1,
                'CardNumber' => $payment['cc_number'],
                'CardExpiration' => $payment['cc_exp_year'] . '-' . $payment['cc_exp_month'],
                'AvsStreet' => $billing['street'][0] . ' ' . $billing['street'][1],
                'AvsZip' => $billing['postcode'],
                'CardCode' => $payment['cc_cid'],
                'CardType' => $payment['cc_type']
            );
            $mage_cust_id = $this->getRequest()->getParam('mage_cust_id');
            $ebzc_cust_id = Mage::getModel('ebizcharge/token')->getCollection()
                    ->addFieldToFilter('mage_cust_id', $mage_cust_id)
                    ->getFirstItem()
                    ->getEbzcCustId()
            ;
            $tran = Mage::getModel('ebizcharge/TranApi');
            if (Mage::getStoreConfig('payment/ebizcharge/sandbox')) {
                $tran->usesandbox = true;
            }
            $tran->key = Mage::getStoreConfig('payment/ebizcharge/sourcekey');
            $tran->pin = Mage::getStoreConfig('payment/ebizcharge/sourcepin');
            $tran->software = 'Mage_Ebizcharge 1.0.2';
            $wsdl = $tran->_getWsdlUrl();
            $ueSecurityToken = $tran->_getUeSecurityToken();
            $client = new SoapClient($wsdl);
            if ($ebzc_cust_id) {
                try {
                    $method_id = $client->addCustomerPaymentMethod($ueSecurityToken, $ebzc_cust_id, $paymentMethod, $default, false);
                    if ($method_id) {
                        Mage::getSingleton('core/session')->addSuccess($this->__('Credit Card saved successfully'));
                    } else {
                        Mage::getSingleton('core/session')->addError($this->__('Unable to obtain method id'));
                    }
                } catch (Exception $ex) {
                    Mage::getSingleton('core/session')->addError($this->__($ex->getMessage()));
                    $this->_redirectReferer();
                    return;
                }
            } else {
                $customerData = array(
                    'CustomerID' => $mage_cust_id,
                    'Enabled' => false,
                    'Amount' => '',
                    'OrderID' => '',
                    'Description' => '',
                    'Next' => '',
                    'Schedule' => '',
                    'NumLeft' => '',
                    'SendReceipt' => '',
                    'ReceiptNote' => '',
                    'BillingAddress' => array(
                        'FirstName' => $billing['firstname'],
                        'LastName' => $billing['lastname'],
                        'Company' => $billing['company'],
                        'Street' => $billing['street'][0],
                        'Street2' => $billing['street'][1],
                        'City' => $billing['city'],
                        'State' => $billing['region'],
                        'Zip' => $billing['postcode'],
                        'Country' => $billing['country'],
                        'Email' => $billing['email'],
                        'Phone' => $billing['phone'],
                    ),
                    'PaymentMethods' => array(
                        array(
                            'CardNumber' => $payment['cc_number'],
                            'CardExpiration' => $payment['cc_exp_year'] . '-' . $payment['cc_exp_month'],
                            'MethodName' => $payment['cc_type'] . ' ' . substr($payment['cc_number'], -4) . ' - ' . $payment['cc_holder'],
                            'SecondarySort' => 0
                        )
                    )
                );
                try {
                    $ebzc_cust_id = $client->addCustomer($ueSecurityToken, $customerData);
                    if ($ebzc_cust_id) {
                        $token = Mage::getModel('ebizcharge/token');
                        $token->setMageCustId($mage_cust_id);
                        $token->setEbzcCustId($ebzc_cust_id);
                        $token->save();
                        Mage::getSingleton('core/session')->addSuccess($this->__('Payment method added successfully'));
                    } else {
                        Mage::getSingleton('core/session')->addError($this->__('Unable to save customer payment method'));
                        $this->_redirectReferer();
                        return;
                    }
                } catch (Exception $ex) {
                    Mage::getSingleton('core/session')->addError($this->__($ex->getMessage()));
                    $this->_redirectReferer();
                    return;
                }
            }
            $this->_redirect('ebizcharge/index/');
            return;
        }
        $this->_redirectReferer();
    }

    public function editAction() {
        $cid = $this->getRequest()->getParam('cid');
        $mid = $this->getRequest()->getParam('mid');
        $method = $this->getRequest()->getParam('method');
        if ($cid && $mid && $method) {
            $this->loadLayout();
            $navigation = $this->getLayout()->getBlock('customer_account_navigation');
            if ($navigation) {
                $navigation->setActive('ebizcharge/index/');
            }
            $this->renderLayout();
        } else {
            $this->_redirectReferer();
        }
    }

    public function editCardPostAction() {
        $cid = $this->getRequest()->getParam('cid');
        $mid = $this->getRequest()->getParam('mid');
        $ccExpMonth = $this->getRequest()->getParam('cc_exp_month');
        $ccExpYear = $this->getRequest()->getParam('cc_exp_year');
        $avsStreet = $this->getRequest()->getParam('avs_street');
        $avszip = $this->getRequest()->getParam('avs_zip');
        $default = $this->getRequest()->getParam('default');
        if ($cid && $mid) {
            $tran = Mage::getModel('ebizcharge/TranApi');
            if (Mage::getStoreConfig('payment/ebizcharge/sandbox')) {
                $tran->usesandbox = true;
            }
            $tran->key = Mage::getStoreConfig('payment/ebizcharge/sourcekey');
            $tran->pin = Mage::getStoreConfig('payment/ebizcharge/sourcepin');
            $tran->software = 'Mage_Ebizcharge 1.0.2';
            $wsdl = $tran->_getWsdlUrl();
            $ueSecurityToken = $tran->_getUeSecurityToken();
            $client = new SoapClient($wsdl);
            try {
                $paymentMethod = $client->getCustomerPaymentMethod($ueSecurityToken, $cid, $mid);
                $paymentMethod->CardNumber = 'XXXXXX' . substr($paymentMethod->CardNumber, 6);
                $paymentMethod->CardExpiration = $ccExpYear . '-' . $ccExpMonth;
                $paymentMethod->AvsStreet = $avsStreet;
                $paymentMethod->AvsZip = $avszip;
                if ($default) {
                    $paymentMethod->SecondarySort = $default ? 0 : 1;
                }
                if ($client->updateCustomerPaymentMethod($ueSecurityToken, $paymentMethod, false)) {
                    Mage::getSingleton('core/session')->addSuccess($this->__('Payment Method updated successfully'));
                    $this->_redirect('ebizcharge/index/');
                    return;
                } else {
                    Mage::getSingleton('core/session')->addError($this->__('Unable to update payment method'));
                }
            } catch (Exception $ex) {
                Mage::getSingleton('core/session')->addError($this->__($ex->getMessage()));
            }
        } else {
            Mage::getSingleton('core/session')->addError($this->__('Invalid payment method id'));
        }
        $this->_redirectReferer();
        return;
    }

    public function deleteAction() {
        if ($this->getRequest()->isPost()) {
            $html = "<option value=''>Please select...</option>\r\n";
            $cid = $this->getRequest()->getParam('cid');
            $mid = $this->getRequest()->getParam('mid');
//            $model = Mage::getModel('ebizcharge/CCPaymentAction');
//            $tran = $model->_initTransaction(new Varien_Object());
            $tran = Mage::getModel('ebizcharge/TranApi');
            if (Mage::getStoreConfig('payment/ebizcharge/sandbox')) {
                $tran->usesandbox = true;
            }
            $tran->key = Mage::getStoreConfig('payment/ebizcharge/sourcekey');
            $tran->pin = Mage::getStoreConfig('payment/ebizcharge/sourcepin');
            $tran->software = 'Mage_Ebizcharge 1.0.2';
            $wsdl = $tran->_getWsdlUrl();
            $ueSecurityToken = $tran->_getUeSecurityToken();
            $client = new SoapClient($wsdl);
            try {
                if ($client->deleteCustomerPaymentMethod($ueSecurityToken, $cid, $mid)) {
                    $methods = $client->getCustomerPaymentMethods($ueSecurityToken, $cid);
                    foreach ($methods as $method) {
                        $html.="<option value='{$method->MethodID}'>{$method->MethodName} - Expires on: {$method->CardExpiration}</option>\r\n";
                    }
                }
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
            echo $html;
        } else {
            echo 'invalid request';
        }
    }

    public function deleteCardAction() {
        $ebzc_cust_id = $this->getRequest()->getParam('cid');
        $method_id = $this->getRequest()->getParam('mid');
        $tran = Mage::getModel('ebizcharge/TranApi');
        if (Mage::getStoreConfig('payment/ebizcharge/sandbox')) {
            $tran->usesandbox = true;
        }
        $tran->key = Mage::getStoreConfig('payment/ebizcharge/sourcekey');
        $tran->pin = Mage::getStoreConfig('payment/ebizcharge/sourcepin');
        $tran->software = 'Mage_Ebizcharge 1.0.2';
        $wsdl = $tran->_getWsdlUrl();
        $ueSecurityToken = $tran->_getUeSecurityToken();
        $client = new SoapClient($wsdl);
        try {
            if ($client->deleteCustomerPaymentMethod($ueSecurityToken, $ebzc_cust_id, $method_id)) {
                Mage::getSingleton('core/session')->addSuccess($this->__('Payment Method deleted successfully'));
            }
        } catch (Exception $ex) {
            Mage::getSingleton('core/session')->addError($this->__($ex->getMessage()));
        }
        $this->_redirectReferer();
    }

}

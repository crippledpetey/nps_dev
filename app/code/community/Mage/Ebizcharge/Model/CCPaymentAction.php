<?php

/**
 * eBizCharge Magento Plugin.
 * v1.0.3 - March 6th, 2013
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

/**
 * Data Helper
 *
 * @category   Mage
 * @package    Mage_Ebizcharge
 * @name       Mage_Ebizcharge_Helper_Data
 * @author     Century Business Solutions <support@centurybizsolutions.com>
 */
class Mage_Ebizcharge_Model_CCPaymentAction extends Mage_Payment_Model_Method_Cc {

	protected $_code = 'ebizcharge';
	protected $_formBlockType = 'ebizcharge/form';
	protected $_isGateway = true;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = true;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid = true;
	protected $_canUseInternal = true;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = true;
	protected $_canSaveCc = false;
	protected $_authMode = 'auto';

	public function validate() {
		$info = $this->getInfoInstance();
		if ($info->getEbzcOption() == 'saved') {
//            $errorMsg = false;
			//            $ccType = $info->getCcType();
			//            if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
			//                $errorMsg = Mage::helper('payment')->__('Incorrect credit card expiration date.');
			//            }
			//
			//            if ($errorMsg) {
			//                Mage::throwException($errorMsg);
			//            }
			return $this;
		} elseif ($info->getEbzcOption() == 'update') {
			if (!$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
				$errorMsg = Mage::helper('payment')->__('Incorrect credit card expiration date.');
			}
			if ($errorMsg) {
				Mage::throwException($errorMsg);
			}
			return $this;
		}
		return parent::validate();
	}

	public function assignData($data) {
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		$info = $this->getInfoInstance();
		if ($data->getEbzcOption() == "saved") {
			$tran = $this->_initTransaction(new Varien_Object());
			$ueSecurityToken = $tran->_getUeSecurityToken();
			$wsdl = $tran->_getWsdlUrl();
			$client = new SoapClient($wsdl);
			try {
				$method = $client->getCustomerPaymentMethod(
					$ueSecurityToken, $data->getEbzcCustId(), $data->getEbzcMethodId());
			} catch (SoapFault $ex) {

				Mage::throwException('SoapFault: ' . $ex->getMessage());
			}
			$info->setEbzcOption($data->getEbzcOption())
			     ->setEbzcMethodId($data->getEbzcMethodId())
			     ->setEbzcCustId($data->getEbzcCustId())
//                    ->setCcType($data->getCcType())
			     ->setCcType($method->CardType)
				->setCcOwner($method->MethodName)
				->setCcLast4(substr($method->CardNumber, -4))
			                                                        ->setCcNumber($method->CardNumber)
//                    ->setCcCid($data->getCcCid())
			//                    ->setCcExpMonth($data->getCcExpMonth())
			//                    ->setCcExpYear($data->getCcExpYear())
			//                    ->setCcSsIssue($data->getCcSsIssue())
			//                    ->setCcSsStartMonth($data->getCcSsStartMonth())
			//                    ->setCcSsStartYear($data->getCcSsStartYear())
			;
//            Mage::throwException($data->getCcNumber());
		} elseif ($data->getEbzcOption() == "update") {
			$tran = $this->_initTransaction(new Varien_Object());
			$ueSecurityToken = $tran->_getUeSecurityToken();
			$wsdl = $tran->_getWsdlUrl();
			$client = new SoapClient($wsdl);
			try {
				$method = $client->getCustomerPaymentMethod(
					$ueSecurityToken, $data->getEbzcCustId(), $data->getEbzcMethodId());
			} catch (SoapFault $ex) {

				Mage::throwException('SoapFault: ' . $ex->getMessage());
			}
//            Mage::throwException($data->getCcExpMonth() . ' ' . $data->getCcExpYear());
			$info->setEbzcOption($data->getEbzcOption())
			     ->setEbzcMethodId($data->getEbzcMethodId())
			     ->setEbzcCustId($data->getEbzcCustId())
			     ->setCcType($method->CardType)
				->setCcOwner($method->MethodName)
				->setCcLast4(substr($method->CardNumber, -4))
			                                                        ->setCcNumber($method->CardNumber)
			             	->setCcExpMonth($data->getCcExpMonth())
				->setCcExpYear($data->getCcExpYear())
				->setEbzcAvsStreet($data->getEbzcAvsStreet())
				->setEbzcAvsZip($data->getEbzcAvsZip())
			;
		} elseif ($data->getEbzcOption() == "new") {
			$info->setCcType($data->getCcType())
			     ->setCcOwner($data->getCcOwner())
			     ->setCcLast4(substr($data->getCcNumber(), -4))
			     ->setCcNumber($data->getCcNumber())
			     ->setCcCid($data->getCcCid())
			     ->setCcExpMonth($data->getCcExpMonth())
			     ->setCcExpYear($data->getCcExpYear())
			     ->setCcSsIssue($data->getCcSsIssue())
			     ->setCcSsStartMonth($data->getCcSsStartMonth())
			     ->setCcSsStartYear($data->getCcSsStartYear())
			     ->setEbzcOption($data->getEbzcOption())
			     ->setEbzcCustId($data->getEbzcCustId())
			     ->setEbzcSavePayment($data->getEbzcSavePayment())
			;
//            Mage::throwException($data->getCcNumber());
		} else {
			$info->setEbzcSavePayment($data->getEbzcSavePayment());
			return parent::assignData($data);
		}
//        Mage::log('data assigned' . date('h:i:s a d-m-Y', time()));
		//        Mage::log($data);
		return $this;
	}

	public function authorize(Varien_Object $payment, $amount) {

		// initialize transaction object
		$tran = $this->_initTransaction($payment);

		// general payment data
		$tran->cardholder = $payment->getCcOwner();
		$tran->card = $payment->getCcNumber();
		$cctype = $payment->getCcType();
		$types = Mage::getSingleton('payment/config')->getCcTypes();
		if (isset($types[$cctype])) {
			$tran->cardtype = $types[$cctype];
		} else {
			$tran->cardtype = $cctype;
		}
//        Mage::log(get_class($payment));
		//        Mage::log($ccTypes);
		//        Mage::throwException($payment->getMethod()->getInfoInstance()->getCcTypeName());
		$tran->exp = $payment->getCcExpMonth() . substr($payment->getCcExpYear(), 2, 2);
		$tran->cvv2 = $payment->getCcCid();
		$tran->amount = $amount;

		if ($this->getConfigData('custreceipt')) {
			$tran->custreceipt = true;
			$tran->custreceipt_template = $this->getConfigData('custreceipt_template');
		}

		// if order exists,  add order data
		$order = $payment->getOrder();
		if (!empty($order)) {

			$orderid = $order->getIncrementId();
			$tran->invoice = $orderid;
			$tran->orderid = $orderid;
			$tran->ponum = $orderid;
			$tran->ip = $order->getRemoteIp();
			$tran->custid = $order->getCustomerId();
			$tran->email = $order->getCustomerEmail();

			$tran->tax = $order->getTaxAmount();
//            Mage::log($order->getTaxAmount());
			$tran->shipping = $order->getShippingAmount();

			// avs data
			list($avsstreet) = $order->getBillingAddress()->getStreet();
			$tran->street = $avsstreet;
			$tran->zip = $order->getBillingAddress()->getPostcode();

			$tran->description = ($this->getConfigData('description') ? str_replace('[orderid]', $orderid, $this->getConfigData('description')) : "Magento Order #" . $orderid);

			// billing info
			$billing = $order->getBillingAddress();
			if (!empty($billing)) {
				$tran->billfname = $billing->getFirstname();
				$tran->billlname = $billing->getLastname();
				$tran->billcompany = $billing->getCompany();
				$tran->billstreet = $billing->getStreet(1);
				$tran->billstreet2 = $billing->getStreet(2);
				$tran->billcity = $billing->getCity();
				$tran->billstate = $billing->getRegion();
				$tran->billzip = $billing->getPostcode();
				$tran->billcountry = $billing->getCountry();
				$tran->billphone = $billing->getTelephone();
				$tran->custid = $billing->getCustomerId();
			}

			// shipping info
			$shipping = $order->getShippingAddress();
//            Mage::log($shipping->getData());
			if (!empty($shipping)) {
				$tran->shipfname = $shipping->getFirstname();
				$tran->shiplname = $shipping->getLastname();
				$tran->shipcompany = $shipping->getCompany();
				$tran->shipstreet = $shipping->getStreet(1);
				$tran->shipstreet2 = $shipping->getStreet(2);
				$tran->shipcity = $shipping->getCity();
				$tran->shipstate = $shipping->getRegion();
				$tran->shipzip = $shipping->getPostcode();
				$tran->shipcountry = $shipping->getCountry();
			}

			// line item data
			foreach ($order->getAllVisibleItems() as $item) {
//                Mage::log($item->getTaxAmount());
				$tran->addLine($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQtyOrdered(), $item->getTaxAmount());
				// for tokenization
				$tran->addLineItem($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQtyOrdered(), $item->getTaxAmount());
			}
		}

		//file_put_contents(tempnam('/tmp','authorize'), print_r($payment,true));
		// switch command based on pref
		if ($this->getConfigData('payment_action') == self::ACTION_AUTHORIZE && $this->_authMode != 'capture')
//            $tran->command = 'cc:authonly';
		{
			$tran->command = 'authonly';
		} else
//            $tran->command = 'cc:sale';
		{
			$tran->command = 'sale';
		}

		//get magento customer session
		//        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'));
		//ueLogDebug("CCPaymentAction::Authorize   Amount: $amount    AuthMode: " . $this->_authMode . "     Command: " . $tran->command . "\n" );
		// process transactions
		//        if (!$session->isLoggedIn()) {
		if (!$order->getCustomerId()) {
			//processing a guest checkout. No user logged in
			//            $tran->Process();
			$tran->RunTransaction();
		} else {
			//a user is logged in
			if ($payment->getEbzcOption() == 'saved') {
				//existing payment method selected by customer
				$tran->SavedProcess($payment->getEbzcCustId(), $payment->getEbzcMethodId());
			} elseif ($payment->getEbzcOption() == 'new') {
				//new method added by customer
				if ($this->getConfigData('save_payment') || $payment->getEbzcSavePayment()) {
					$tran->NewPaymentProcess($payment->getEbzcCustId());
				} else {
					$tran->RunTransaction();
				}
			} elseif ($payment->getEbzcOption() == 'update') {
				$tran->UpdateProcess($payment->getEbzcCustId(), $payment->getEbzcMethodId(), $payment);
			} else {
				//first time processing the transaction
				if ($this->getConfigData('save_payment') || $payment->getEbzcSavePayment()) {
					$tran->TokenProcess($order->getCustomerId());
				} else {
					$tran->RunTransaction();
				}
			}
		}

//        Mage::log('ref num: ' . $tran->refnum);
		// store response variables
		$payment->setCcApproval($tran->authcode)
			->setCcTransId($tran->refnum)
			->setCcAvsStatus($tran->avs_result_code)
			->setCcCidStatus($tran->cvv2_result_code);

		$payment->getMethodInstance()->getInfoInstance()->setEbzcCustId($tran->custnum);
		if ($tran->resultcode == 'A') {
			if ($this->getConfigData('payment_action') == self::ACTION_AUTHORIZE) {
				$payment->setLastTransId('0');
			} else {
				$payment->setLastTransId($tran->refnum);
			}

			if (!$payment->getParentTransactionId() ||
				$tran->refnum != $payment->getParentTransactionId()) {
				$payment->setTransactionId($tran->refnum);
			}
			$payment->setIsTransactionClosed(0)
			        ->setTransactionAdditionalInfo('trans_id', $tran->refnum);

			$payment->setStatus(self::STATUS_APPROVED);

			//ueLogDebug("CCPaymentAction::Authorize  Approved" );
		} elseif ($tran->resultcode == 'D') {

			//ueLogDebug("CCPaymentAction::Authorize  Declined" );

			Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
		} else {

			//ueLogDebug("CCPaymentAction::Authorize  Error" );

			Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '(' . $tran->errorcode . ')'));
		}

		return $this;
	}

	public function quicksale(Varien_Object $payment, $amount) {

		// initialize transaction object
		$tran = $this->_initTransaction($payment);

		if (!$payment->getLastTransId()) {
			Mage::throwException(Mage::helper('paygate')->__('Unable to find previous transaction to reference'));
		}

		// payment data
		//        $tran->refnum = $payment->getLastTransId();
		$tran->refnum = $payment->getCcTransId();
		$tran->amount = $amount;
		$tran->ponum = $payment->getPoNumber();

		if ($this->getConfigData('sandbox')) {
			$tran->custreceipt = true;
			$tran->custreceipt_template = $this->getConfigData('custreceipt_template');
		}

		// if order exists,  add order data
		$order = $payment->getOrder();
		if (!empty($order)) {

			$orderid = $order->getIncrementId();
			$tran->invoice = $orderid;
			$tran->orderid = $orderid;
			$tran->ip = $order->getRemoteIp();
			$tran->email = $order->getCustomerEmail();

			$tran->tax = $order->getTaxAmount();
			$tran->shipping = $order->getShippingAmount();

			// avs data
			list($avsstreet) = $order->getBillingAddress()->getStreet();
			$tran->street = $avsstreet;
			$tran->zip = $order->getBillingAddress()->getPostcode();

			$tran->description = ($this->getConfigData('description') ? str_replace('[orderid]', $orderid, $this->getConfigData('description')) : "Magento Order #" . $orderid);

			// billing info
			$billing = $order->getBillingAddress();
			if (!empty($billing)) {
				$tran->billfname = $billing->getFirstname();
				$tran->billlname = $billing->getLastname();
				$tran->billcompany = $billing->getCompany();
				$tran->billstreet = $billing->getStreet(1);
				$tran->billstreet2 = $billing->getStreet(2);
				$tran->billcity = $billing->getCity();
				$tran->billstate = $billing->getRegion();
				$tran->billzip = $billing->getPostcode();
				$tran->billcountry = $billing->getCountry();
				$tran->billphone = $billing->getTelephone();
				$tran->custid = $billing->getCustomerId();
			}

			// shipping info
			$shipping = $order->getShippingAddress();
			if (!empty($shipping)) {
				$tran->shipfname = $shipping->getFirstname();
				$tran->shiplname = $shipping->getLastname();
				$tran->shipcompany = $shipping->getCompany();
				$tran->shipstreet = $shipping->getStreet(1);
				$tran->shipstreet2 = $shipping->getStreet(2);
				$tran->shipcity = $shipping->getCity();
				$tran->shipstate = $shipping->getRegion();
				$tran->shipzip = $shipping->getPostcode();
				$tran->shipcountry = $shipping->getCountry();
			}
		}

		//file_put_contents(tempnam('/tmp','quicksale'), print_r($payment,true));
		//ueLogDebug("Sending quicksale for $amount on prior transid {$tran->refnum}");
		$tran->command = 'capture';

		if ($order->hasInvoices()) {
			foreach ($order->getInvoiceCollection() as $invoice) {
				foreach ($invoice->getAllItems() as $item) {
					$tran->addLine($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQty(), $item->getTaxAmount());
					// for tokenization
					$tran->addLineItem($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQty(), $item->getTaxAmount());
				}
			}
		}
//
		//        // process transactions
		//        $tran->Process();
		$tran->CaptureTransaction($tran->refnum, $amount);
		// store response variables
		//        $payment->setCcApproval($tran->authcode)
		//                ->setCcTransId($tran->refnum)
		//                ->setCcAvsStatus($tran->avs_result_code)
		//                ->setCcCidStatus($tran->cvv2_result_code);
		// ueLogDebug("Tran:" . print_r($tran, true));

		if ($tran->resultcode == 'A') {
			if ($tran->refnum) {
				$payment->setLastTransId($tran->refnum);
			}

			$payment->setStatus(self::STATUS_APPROVED);

			if (!$payment->getParentTransactionId() ||
				$tran->refnum != $payment->getParentTransactionId()) {
				$payment->setTransactionId($tran->refnum);
			}
			$payment->setIsTransactionClosed(0)
			        ->setTransactionAdditionalInfo('trans_id', $tran->refnum);
			//ueLogDebug("Transaction Approved");
		} elseif ($tran->resultcode == 'D') {
			//ueLogDebug("Transaction Declined");
			Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
		} else {
			//ueLogDebug("Transaction Error");
			Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '(' . $tran->errorcode . ')'));
		}

		return $this;
	}

	public function refund(Varien_Object $payment, $amount) {

		// ueLogDebug("CCPaymentAction::refund amount: $amount  transid: " . $payment->getLastTransId());
		$error = false;

		$orderid = $payment->getOrder()->getIncrementId();

		list($avsstreet) = $payment->getOrder()->getBillingAddress()->getStreet();
		$avszip = $payment->getOrder()->getBillingAddress()->getPostcode();

		$tran = $this->_initTransaction($payment);

		if (!$payment->getLastTransId()) {
			Mage::throwException(Mage::helper('paygate')->__('Unable to find previous transaction to reference'));
		}

		// payment data
		$tran->refnum = $payment->getLastTransId();
		$tran->amount = $amount;
		$tran->invoice = $orderid;
		$tran->orderid = $orderid;
		$tran->cardholder = $payment->getCcOwner();
		$tran->street = $avsstreet;
		$tran->zip = $avszip;
		$tran->description = "Online Order";
		$tran->cvv2 = $payment->getCcCid();
		$tran->command = 'quickcredit';

//        if (!$tran->Process()) {
		if (!$tran->RefundTransaction($tran->refnum, $tran->amount)) {
			$payment->setStatus(self::STATUS_ERROR);
			$error = Mage::helper('paygate')->__('Error in authorizing the payment: ' . $tran->error);
			Mage::throwException('Payment Declined: ' . $tran->error . $tran->errorcode);
		} else {
			$payment->setStatus(self::STATUS_APPROVED);
			if ($tran->refnum != $payment->getParentTransactionId()) {
				$payment->setTransactionId($tran->refnum);
			}
			$shouldCloseCaptureTransaction = $payment->getOrder()->canCreditmemo() ? 0 : 1;
			$payment->setIsTransactionClosed(1)
			        ->setShouldCloseParentTransaction($shouldCloseCaptureTransaction)
			        ->setTransactionAdditionalInfo('trans_id', $tran->refnum);
		}

		if ($error !== false) {
			Mage::throwException($error);
		}
		return $this;
	}

	public function capture(Varien_Object $payment, $amount) {

		//file_put_contents(tempnam('/tmp','capture'), print_r($payment,true));
		//ueLogDebug("CCPaymentAction::Capture  Amount: $amount CcTransId: " . $payment->getCcTransId() . "    LastTransId: " . $payment->getLastTransId() . "  TotalPaid:   " . $payment->getOrder()->getTotalPaid() . "  Cardnumber(doh):" . $payment->getCcNumber() . "\n");
		// we have already captured the original auth,  we need to do full sale
		if ($payment->getLastTransId() && $payment->getOrder()->getTotalPaid() > 0) {
			return $this->quicksale($payment, $amount);
		}
		// if we don't have a transid than we are need to authorize
		if (!$payment->getParentTransactionId()) {
			$this->_authMode = 'capture';
			return $this->authorize($payment, $amount);
		}

		$tran = $this->_initTransaction($payment);
		$tran->command = 'cc:capture';
//        $tran->command = 'capture';
		$tran->refnum = $payment->getCcTransId();

		$tran->amount = $amount;

		$order = $payment->getOrder();
		if (!empty($order)) {

//            if ($order->hasInvoices()) {
			//                $invoice = $order->getInvoiceCollection()->getLastItem();
			//                Mage::log($order->getInvoiceCollection()->getLastItem()->getData());
			//                Mage::throwException('invoice: ' . $invoice->getId());
			//            } else {
			//                Mage::throwException('no invoice in the order');
			//            }
			$orderid = $order->getIncrementId();
			$tran->invoice = $orderid;
			$tran->orderid = $orderid;
			$tran->ponum = $orderid;
			$tran->ip = $order->getRemoteIp();
			$tran->custid = $order->getCustomerId();
			$tran->email = $order->getCustomerEmail();

			$tran->tax = $order->getTaxAmount();
			$tran->shipping = $order->getShippingAmount();

			// avs data
			list($avsstreet) = $order->getBillingAddress()->getStreet();
			$tran->street = $avsstreet;
			$tran->zip = $order->getBillingAddress()->getPostcode();

			$tran->description = ($this->getConfigData('description') ? str_replace('[orderid]', $orderid, $this->getConfigData('description')) : "Magento Order #" . $orderid);

			// billing info
			$billing = $order->getBillingAddress();
			if (!empty($billing)) {
				$tran->billfname = $billing->getFirstname();
				$tran->billlname = $billing->getLastname();
				$tran->billcompany = $billing->getCompany();
				$tran->billstreet = $billing->getStreet(1);
				$tran->billstreet2 = $billing->getStreet(2);
				$tran->billcity = $billing->getCity();
				$tran->billstate = $billing->getRegion();
				$tran->billzip = $billing->getPostcode();
				$tran->billcountry = $billing->getCountry();
				$tran->billphone = $billing->getTelephone();
				$tran->custid = $billing->getCustomerId();
			}

			// shipping info
			$shipping = $order->getShippingAddress();
//            Mage::log($shipping->getData());
			if (!empty($shipping)) {
				$tran->shipfname = $shipping->getFirstname();
				$tran->shiplname = $shipping->getLastname();
				$tran->shipcompany = $shipping->getCompany();
				$tran->shipstreet = $shipping->getStreet(1);
				$tran->shipstreet2 = $shipping->getStreet(2);
				$tran->shipcity = $shipping->getCity();
				$tran->shipstate = $shipping->getRegion();
				$tran->shipzip = $shipping->getPostcode();
				$tran->shipcountry = $shipping->getCountry();
			}
//            echo '<pre>';
			//            var_dump($order->hasInvoices());die;
			// line item data
			if ($order->hasInvoices()) {
				foreach ($order->getInvoiceCollection() as $invoice) {
					foreach ($invoice->getAllItems() as $item) {
						$tran->addLine($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQty(), $item->getTaxAmount());
						// for tokenization
						$tran->addLineItem($item->getSku(), $item->getName(), '', $item->getPrice(), $item->getQty(), $item->getTaxAmount());
					}
				}
			}
		}
//        print_r($tran->lineItems);
		//        die;
		// process transaction
		//$tran->Process();
		$tran->CaptureTransaction($tran->refnum, $tran->amount);

		// look at result code
		if ($tran->resultcode == 'A') {
			$payment->setStatus(self::STATUS_APPROVED);
			$payment->setLastTransId($tran->refnum);

			if (!$payment->getParentTransactionId() ||
				$tran->refnum != $payment->getParentTransactionId()) {
				$payment->setTransactionId($tran->refnum);
			}
			$payment->setIsTransactionClosed(0)
			        ->setTransactionAdditionalInfo('trans_id', $tran->refnum);

			return $this;
		} elseif ($tran->resultcode == 'D') {
			Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
		} else {
			Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '(' . $tran->errorcode . ')'));
		}
	}

	public function canVoid(Varien_Object $payment) {
//        return $this->_canVoid;
		return true;
	}

	public function void(Varien_Object $payment) {
		//ueLogDebug("CCPaymentAction::refund amount: $amount  transid: " . $payment->getLastTransId());

		if ($payment->getCcTransId()) {
			$tran = $this->_initTransaction($payment);
			$tran->command = 'creditvoid';
			$tran->refnum = $payment->getCcTransId();

			// process transactions
			$tran->Process();

			if ($tran->resultcode == 'A') {
				$payment->setStatus(self::STATUS_SUCCESS);
			} elseif ($tran->resultcode == 'D') {
				$payment->setStatus(self::STATUS_ERROR);
				Mage::throwException(Mage::helper('paygate')->__('Payment authorization transaction has been declined:  ' . $tran->error));
			} else {
				$payment->setStatus(self::STATUS_ERROR);
				Mage::throwException(Mage::helper('paygate')->__('Payment authorization error:  ' . $tran->error . '(' . $tran->errorcode . ')'));
			}
		} else {
			$payment->setStatus(self::STATUS_ERROR);
			Mage::throwException(Mage::helper('paygate')->__('Invalid transaction id '));
		}
		return $this;
	}

	public function cancel(Varien_Object $payment) {
		if ($payment->getCcTransId()) {
			$tran = $this->_initTransaction($payment);
			$tran->refnum = $payment->getCcTransId();
			if ($tran->VoidTransaction($tran->refnum)) {
				return $this;
			} else {
				Mage::throwException('Transaction not void');
			}

		} else {
			$payment->setStatus(self::STATUS_ERROR);
			Mage::throwException(Mage::helper('ebizcharge')->__('Invalid transaction id'));
		}
		return $this;
	}

	/**
	 * Setup the ebizcharge transaction api class.
	 *
	 * Much of this code is common to all commands
	 *
	 * @param Mage_Sales_Model_Document $pament
	 * @return Mage_Ebizcharge_Model_TranApi
	 */
	protected function _initTransaction(Varien_Object $payment) {
		$tran = Mage::getModel('ebizcharge/TranApi');

		if ($this->getConfigData('sandbox')) {
			$tran->usesandbox = true;
		}

		$tran->key = $this->getConfigData('sourcekey');
		$tran->pin = $this->getConfigData('sourcepin');
		$tran->software = 'Mage_Ebizcharge 1.0.2';
		return $tran;
	}

}

/*
function ueLogDebug($mesg)
{
global $debugfd;

if(!$debugfd) {
$debugfd = fopen('/tmp/uelog','a');
}

fwrite($debugfd, $mesg. "\n");

}
 */
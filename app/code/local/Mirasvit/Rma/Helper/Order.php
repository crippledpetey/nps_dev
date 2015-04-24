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


class Mirasvit_Rma_Helper_Order
{

    public function canCreateCreditmemo($rma)
    {
        $order = $rma->getOrder();
        if (!$order->canCreditmemo() || $rma->getCreditMemoId()) {
            return false;
        }
        $haveItems = false;
        if (!$status = Mage::helper('rma')->getResolutionByCode('refund')) {
            return false;
        }
        $refundResolutionId = $status->getId();
        foreach ($rma->getItemCollection() as $item) {
            if ($item->getResolutionId() != $refundResolutionId) {
                continue;
            }
            $haveItems = true;
        }
        return $haveItems;
    }

    public function canCreateExchangeOrder($rma)
    {
        $haveItems = false;
        if (!$status = Mage::helper('rma')->getResolutionByCode('exchange')) {
            return false;
        }
        $exchangeResolutionId = $status->getId();
        foreach ($rma->getItemCollection() as $item) {
            if ($item->getResolutionId() != $exchangeResolutionId) {
                continue;
            }
            $haveItems = true;
        }
        return $haveItems;
    }

	public function createExchangeOrder($rma)
	{
        $origOrder = $rma->getOrder();
        $customer = $rma->getCustomer();

        $transaction = Mage::getModel('core/resource_transaction');
        $storeId = $origOrder->getStoreId();
        $reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);

        $order = Mage::getModel('sales/order')
            ->setIncrementId($reservedOrderId)
            ->setStoreId($storeId)
            ->setQuoteId(0)
            ->setGlobalCurrencyCode($origOrder->getGlobalCurrencyCode())
            ->setBaseCurrencyCode($origOrder->getBaseCurrencyCode())
            ->setStoreCurrencyCode($origOrder->getStoreCurrencyCode())
            ->setOrderCurrencyCode($origOrder->getOrderCurrencyCode());

        // set Customer data
        $order->setCustomerEmail($customer->getEmail())
            ->setCustomerFirstname($customer->getFirstname())
            ->setCustomerLastname($customer->getLastname())
            ->setCustomerGroupId($customer->getGroupId())
            ->setCustomerIsGuest(0)
            ->setCustomer($customer);

        // set Billing Address
        $billing = $customer->getDefaultBillingAddress();
        $data = $origOrder->getBillingAddress()->getData();
        unset($data['entity_id']);
        $billingAddress = Mage::getModel('sales/order_address')
            ->setData($data);
        $order->setBillingAddress($billingAddress);

        if ($origOrder->getShippingAddress()) {
            $data = $origOrder->getShippingAddress()->getData();
            unset($data['entity_id']);
            $shippingAddress = Mage::getModel('sales/order_address')
                ->setData($data);
            $order->setShippingAddress($shippingAddress)
                ->setShipping_method('flatrate_flatrate');
            /*->setShippingDescription($this->getCarrierName('flatrate'));*/
            /*some error i am getting here need to solve further*/
        }
        //you can set your payment method name here as per your need
        $orderPayment = Mage::getModel('sales/order_payment')
            ->setStoreId($storeId)
            ->setCustomerPaymentId(0)
            ->setMethod('purchaseorder')
            // ->setPo_number(' – ')
            ;
        $order->setPayment($orderPayment);

        // let say, we have 2 products
        //check that your products exists
        //need to add code for configurable products if any
        $subTotal = 0;
        $haveItems = false;
        $exchangeResolutionId = Mage::helper('rma')->getResolutionByCode('exchange')->getId();
        foreach ($rma->getItemCollection() as $item) {
            if ($item->getResolutionId() != $exchangeResolutionId) {
                continue;
            }
            $product = $item->getProduct();
            $qty = $item->getQtyRequested();
            $rowTotal = 0;

            $orderItem = Mage::getModel('sales/order_item')
                ->setStoreId($storeId)
                ->setQuoteItemId(0)
                ->setQuoteParentItemId(NULL)
                ->setProductId($product->getId())
                ->setProductType($product->getTypeId())
                ->setQtyBackordered(NULL)
                ->setTotalQtyOrdered($qty)
                ->setQtyOrdered($qty)
                ->setName($product->getName())
                ->setSku($product->getSku())
                ->setPrice($product->getPrice())
                ->setBasePrice($product->getPrice())
                ->setOriginalPrice($product->getPrice())
                ->setRowTotal($rowTotal)
                ->setBaseRowTotal($rowTotal);

            $subTotal += $rowTotal;
            $order->addItem($orderItem);
            $haveItems = true;
        }
        if (!$haveItems) {
        	throw new Mage_Core_Exception("RMA does not have items with Exchange Resolution");
        }

        $order->setSubtotal($subTotal)
            ->setBaseSubtotal($subTotal)
            ->setGrandTotal($subTotal)
            ->setBaseGrandTotal($subTotal);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));
        $transaction->save();
        $rma->setExchangeOrderId($order->getId())
            ->save();
        if ($this->getConfig()->getGeneralIsSendExchangeOrderConfirmation()) {
            $order->sendNewOrderEmail();
        }
	}

    public function createCreditMemo($rma)
    {
        $order = $rma->getOrder();
        if (!$order->canCreditmemo()) {
            throw new Mage_Core_Exception("You can't create a Credit Memo for order of this RMA");
        }

        $service = Mage::getModel('rma/service_order', $order);
        $data = array(
            'items' => array(),
            'qtys' => array(),
            'comment_text' => 'auto created credit memo',
            'shipping_amount' => 0,
            'adjustment_positive' => 0,
            'adjustment_negative' => 0,
        );
        $refundResolutionId = Mage::helper('rma')->getResolutionByCode('refund')->getId();
        $haveItems = false;
        foreach ($rma->getItemCollection() as $item) {
            if ($item->getResolutionId() != $refundResolutionId) {
                continue;
            }
            $orderItemId = $item->getOrderItemId();
            $orderItem = Mage::getModel('sales/order_item')->load($orderItemId);
            if ($item->getProduct()->isConfigurable()) {
                $data['qtys'][$orderItem->getId()] = $item->getQtyRequested();
                $data['items'][$orderItem->getId()] = array('qty' => $item->getQtyRequested());
                $collection = Mage::getModel('sales/order_item')->getCollection()
                                ->addFieldToFilter('parent_item_id', $orderItem->getId());
                $orderItem = $collection->getFirstItem();
            }
            $data['qtys'][$orderItem->getId()] = $item->getQtyRequested();
            $data['items'][$orderItem->getId()] = array('qty' => $item->getQtyRequested());

            $haveItems = true;
        }
        if (!$haveItems) {
            throw new Mage_Core_Exception("RMA does not have items with Refund Resolution");
        }

        //$creditmemo = $service->prepareCreditmemo($data);
        $invoice = $this->getInvoiceByOrder($order);
        $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);

        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            foreach ($rma->getItemCollection() as $item) {
                if ($item->getProductId() == $creditmemoItem->getProductId()) {
                    $creditmemoItem->setBackToStock($item->getToStock());
                    if ($item->getToStock()) {
                        foreach ($creditmemo->getAllItems() as $creditmemoItem2) {
                            if ($creditmemoItem->getSku() === $creditmemoItem2->getSku()) {
                                $creditmemoItem2->setBackToStock($item->getToStock());
                            }
                        }
                    }
                    break;
                }
            }
        }
// pr($data);die;
//
        if ($this->isOnlineRefundAllowed($creditmemo)) {
            $creditmemo->setOfflineRequested(false);
        } else {
            $creditmemo->setOfflineRequested(true);
        }
        $creditmemo->setRefundRequested(true);
        $this->setupAdjustment($creditmemo, $order);
        $this->setupShippingFee($creditmemo, $order);

        $creditmemo->setGrandTotal(0);
        $creditmemo->setBaseGrandTotal(0);
        $creditmemo->setBaseTaxAmount(0);
        $creditmemo->setTaxAmount(0);
        $creditmemo->collectTotals();
        $creditmemo->register();

        $this->_saveCreditmemo($creditmemo);

        $rma->setCreditMemoId($creditmemo->getId())
            ->save();
    }

    protected function isOnlineRefundAllowed($creditmemo)
    {
        if (!$this->getConfig()->getGeneralIsOnlineRefund()) {
            return false;
        }
        if ($creditmemo->canRefund()) {
            if ($creditmemo->getInvoice() && $creditmemo->getInvoice()->getTransactionId()) {
                return true;
            }
        }
        return false;
    }


    protected function _saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }

    public function setupAdjustment($creditmemo, $order)
    {
        $fee = Mage::getSingleton('rma/config')->getGeneralCreditMemoAdjustmentFee();
        if ($fee == '') {
            return;
        }
        if (strpos($fee, '%')) {
            $fee = str_replace('%', '', trim($fee));
            $fee = floatval($fee);
            $fee = $fee/100 * $creditmemo->getSubtotal();
        } else {
            $fee = floatval($fee);
        }
        if ($fee != 0 && $creditmemo->getSubtotal() > $fee) {
            $creditmemo->setAdjustmentNegative($fee);
        }
    }

    public function setupShippingFee($creditmemo, $order)
    {
        $baseAllowedAmount = $order->getBaseShippingAmount()-$order->getBaseShippingRefunded();
        if ($this->getConfig()->getGeneralIsRefundShippingFee()) {
            $creditmemo->setBaseShippingAmount($baseAllowedAmount);
        } else {
            $creditmemo->setBaseShippingAmount(0);
        }
    }


    public function getInvoiceByOrder($order)
    {
        $collection = Mage::getModel('sales/order_invoice')->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }

    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }
}
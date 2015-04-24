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


class Mirasvit_Rma_GuestController extends Mage_Core_Controller_Front_Action
{

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function _initOrder()
    {
        if (($orderId = Mage::app()->getRequest()->getParam('order_increment_id')) &&
            ($email = Mage::app()->getRequest()->getParam('email'))) {
        	$orderId = trim($orderId);
        	$orderId = str_replace('#', '', $orderId);
            $collection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('increment_id', $orderId);
                ;
            if ($collection->count()) {
                $order =  $collection->getFirstItem();
                $email = trim(strtolower($email));
                if ($email != strtolower($order->getCustomerEmail())
                    && $email != strtolower($order->getCustomerLastname())) {
                    return false;
                }
                return $order;
            }
        }
    }

    public function newAction()
    {
        $session  = $this->_getSession();
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	if ($customer->getId()) {
    		$this->_redirect('rma/rma/new');
    		return;
    	}
        try {
            $order = $this->_initOrder();
            if ($order) {
                $this->_getSession()->setRMAGuestOrderId($order->getId());
                $this->_redirect('rma/guest/list');
                return;
            } elseif (Mage::app()->getRequest()->getParam('order_increment_id')) {
                throw new Mage_Core_Exception(Mage::helper('rma')->__("Wrong Order #, Email or Last Name"));
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function listAction()
    {
        $orderId = $this->_getSession()->getRMAGuestOrderId();
        if (!$orderId) {
            $this->_redirect('rma/guest/new');
            return;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::register('current_order', $order);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function createAction()
    {
        $orderId = $this->_getSession()->getRMAGuestOrderId();
        if (!$orderId) {
            $this->_redirect('rma/guest/new');
            return;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::register('current_order', $order);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    protected function _initRma()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $rma = Mage::getModel('rma/rma')->getCollection()
              ->addFieldToFilter('main_table.guest_id', $id)
              ->getFirstItem();

            if ($rma->getId() > 0) {
                Mage::register('current_rma', $rma);
                Mage::register('external_rma', true);
                return $rma;
            }
        }
    }

    public function viewAction()
    {
        if ($rma = $this->_initRma()) {
            if ($this->getRequest()->getParam('shipping_confirmation')) {
                $rma->confirmShipping();
            }
            $this->markAsRead($rma);
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
        } else {
            $this->_forward('no_rote');
        }
    }

    public function saveAction()
    {
        $session  = $this->_getSession();
        $data = $this->getRequest()->getParams();
        $items = $data['items'];
        unset($data['items']);

        try {
            if ($session->getRMAGuestOrderId() != $data['order_id']) {
                throw new Mage_Core_Exception("Error Processing Request", 1);
            }

            $rma = Mage::helper('rma/process')->createRmaFromPost($data, $items);
            $session->addSuccess($this->__('RMA was successfuly created'));
            $this->_redirectUrl($rma->getGuestUrl());
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $session->setFormData($data);
            $this->_redirect('*/*/*');
        }
    }

    public function savecommentAction()
    {
        $session  = $this->_getSession();
        if (!$rma = $this->_initRma()) {
            throw new Mage_Core_Exception("Error Processing Request", 1);
        }

        try {
            $isConfirmShipping = $this->getRequest()->getParam('shipping_confirmation');
            /// we need to confirm shipping BEFORE posting comment
            /// (comment can be from custom variables value in the shipping confirmation dialog)
            if ($isConfirmShipping) {
                $rma->confirmShipping();
                $session->addSuccess(Mage::helper('rma')->__('Shipping is confirmed. Thank you!'));
            }
            Mage::helper('rma/process')->createCommentFromPost($rma, $this->getRequest()->getParams());

            if (!$isConfirmShipping) {
                $session->addSuccess($this->__('Your comment was successfuly added'));
            }
            $this->_redirectUrl($rma->getGuestUrl());
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirect('*/*/index');
        }
    }

    public function printAction()
    {
        if (!$rma = $this->_initRma()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }

    public function printlabelAction()
    {
        if (!$rma = $this->_initRma()) {
            return;
        }

        if ($label = $rma->getReturnLabel()) {
            $this->_redirectUrl($label->getUrl());
        } else {
            $this->_forward('no_rote');
        }
    }

    protected function markAsRead($rma) {
        if ($comment = $rma->getLastComment()) {
            $comment->setIsRead(true)->save();
        }
    }
}
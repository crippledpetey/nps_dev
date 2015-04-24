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



class Mirasvit_Rma_Block_Rma_New extends Mage_Core_Block_Template
{
	protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(Mage::helper('rma')->__('Create RMA'));
        }
    }

    protected function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    protected function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getOrderCollection()
    {
        return Mage::helper('rma')->getAllowedOrderCollection($this->getCustomer());
    }

    protected $_order;
    public function getOrder()
    {
        if (!$this->_order) {
            if ($orderId = Mage::app()->getRequest()->getParam('order_id')) {
                $collection = Mage::helper('rma')->getAllowedOrderCollection($this->getCustomer(), false);
                $collection->addFieldToFilter('entity_id', (int)$orderId);
                if ($collection->count()) {
                    $this->_order = $collection->getFirstItem();
                }
            }
        }
        return $this->_order;
    }

    public function getStep1PostUrl()
    {
        return Mage::getUrl('rma/rma/new');
    }

    public function getStep2PostUrl()
    {
        return Mage::getUrl('rma/rma/save');
    }


    public function getOrderItemCollection()
    {
        $order = $this->getOrder();
        $collection = $order->getItemsCollection();
        return $collection;
    }

    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    public function getReasonCollection()
    {
        return Mage::getModel('rma/reason')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setStoreId($this->getStoreId())
            ->setOrder('sort_order', 'asc');
    }

    public function getResolutionCollection()
    {
        return Mage::getModel('rma/resolution')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    public function getConditionCollection()
    {
        return Mage::getModel('rma/condition')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    public function getCustomFields()
    {
        $collection = Mage::helper('rma/field')->getVisibleCustomerCollection('initial', true);
        return $collection;
    }

    public function getPolicyIsActive()
    {
        return $this->getConfig()->getPolicyIsActive();
    }

    protected $_pblock;
    public function getPolicyBlock()
    {
        if (!$this->_pblock) {
            $this->_pblock = Mage::getModel('cms/block')->load($this->getConfig()->getPolicyPolicyBlock());
        }
        return $this->_pblock;
    }

    public function getPolicyTitle()
    {
        return $this->getPolicyBlock()->getTitle();
    }

    public function getPolicyContent()
    {
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();

        return $processor->filter($this->getPolicyBlock()->getContent());
    }

    public function getReturnPeriod()
    {
        return $this->getConfig()->getPolicyReturnPeriod();
    }

    public function getIsGift()
    {
        return Mage::app()->getRequest()->getParam('is_gift') == 1;
    }

    public function getRmaItemsByOrderItem($orderItem)
    {
        $collection = Mage::getModel('rma/item')->getCollection();
        $collection->addFieldToFilter('order_item_id', $orderItem->getId());
        // echo $collection->getSelect();die;
        return $collection;
    }

    public function getRMAUrl($rma){
        return $rma->getUrl();
    }

    public function getRmasByOrderItem($orderItem) {
        $result = array();
        foreach ($this->getRmaItemsByOrderItem($orderItem) as $item) {
            $rma = Mage::getModel('rma/rma')->load($item->getRmaId());
            $result[] =  "<a href='{$this->getRMAUrl($rma)}' target='_blank'>#{$rma->getIncrementId()}</a>";
        }
        return implode(', ', $result);
    }
}
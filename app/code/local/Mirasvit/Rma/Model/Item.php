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


class Mirasvit_Rma_Model_Item extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('rma/item');
    }

    public function toOptionArray($emptyOption = false)
    {
    	return $this->getCollection()->toOptionArray($emptyOption);
    }

    protected $_product = null;
    public function getProduct()
    {
        if (!$this->getProductId()) {
            return false;
        }
    	if ($this->_product === null) {
            $this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
            if($this->_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $options = $this->getProductOptions();
                if(isset($options['simple_sku'])) {
                    $this->_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $options['simple_sku']);
                }
            }
    	}
    	return $this->_product;
    }

    protected $_reason = null;
    public function getReason()
    {
        if (!$this->getReasonId()) {
            return false;
        }
    	if ($this->_reason === null) {
            $this->_reason = Mage::getModel('rma/reason')->load($this->getReasonId());
    	}
    	return $this->_reason;
    }

    protected $_resolution = null;
    public function getResolution()
    {
        if (!$this->getResolutionId()) {
            return false;
        }
    	if ($this->_resolution === null) {
            $this->_resolution = Mage::getModel('rma/resolution')->load($this->getResolutionId());
    	}
    	return $this->_resolution;
    }

    protected $_condition = null;
    public function getCondition()
    {
        if (!$this->getConditionId()) {
            return false;
        }
    	if ($this->_condition === null) {
            $this->_condition = Mage::getModel('rma/condition')->load($this->getConditionId());
    	}
    	return $this->_condition;
    }

    protected $_rma = null;
    public function getRma()
    {
        if (!$this->getRmaId()) {
            return false;
        }
    	if ($this->_rma === null) {
            $this->_rma = Mage::getModel('rma/rma')->load($this->getRmaId());
    	}
    	return $this->_rma;
    }

	/************************/
    protected $_stockQty;
    public function getQtyStock()
    {
        if (!$this->_stockQty) {
            $product = $this->getProduct();
            $this->_stockQty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
        }
        return $this->_stockQty;
    }

    protected $_orderItem;
    public function getOrderItem()
    {
        if (!$this->_orderItem) {
            $this->_orderItem = Mage::getModel('sales/order_item')->load($this->getOrderItemId());
        }
        return $this->_orderItem;
    }

    public function getQtyOrdered()
    {
        return (int)$this->getOrderItem()->getQtyOrdered();
    }


    public function initFromOrderItem($orderItem)
    {
        $this->_orderItem = $orderItem;
        $this->setOrderItemId($orderItem->getId());
        $this->setProductId($orderItem->getProductId());
        $this->setName($orderItem->getName());
        $this->setProductOptions($orderItem->getProductOptions());
        $this->setProductType($orderItem->getProductType());
        $qtyShipped = $orderItem->getQtyShipped();
        if (!$product = $orderItem->getProduct()) { //magento 1.6 does not have this method
            if ($productId = $orderItem->getProductId()) {
                $product = Mage::getModel('catalog/product')->load($productId);
            }
        }
        $status = $product->getRmaStatus();

        $this->setIsRmaAllowed((string)$status !== '0');

        // we have option to allow rma when status is processing (for example). so products are not shipped yet.
        if ($qtyShipped == 0) {
            $qtyShipped = $orderItem->getQtyOrdered();
        }
        $qty = $qtyShipped - $this->getQtyInRma($orderItem);
        if ($qty < 0) {
            $qty = 0;
        }
        $this->setQtyAvailable($qty);

        //we need this to avoid error of mysql foreign key
        if (!$this->getReasonId()) {
            $this->setReasonId(null);
        }
        if (!$this->getResolutionId()) {
            $this->setResolutionId(null);
        }
        if (!$this->getConditionId()) {
            $this->setConditionId(null);
        }
        return $this;
    }

    protected function getQtyInRma($orderItem)
    {
        $collection = Mage::getModel('rma/item')->getCollection();
        $collection->addFieldToFilter('order_item_id', $orderItem->getId());
        // echo $collection->getSelect();die;
        $sum = 0;
        foreach($collection as $item) {
            $sum += $item->getQtyRequested();
        }
        return $sum;
    }

    public function getProductOptions()
    {
        $options = $this->getData('product_options');
        if (is_string($options)){
            $options = @unserialize($options);
            $this->setData('product_options', $options);
        }
        return $options;
    }

    public function getReasonName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'reason_name');
    }

    public function getConditionName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'condition_name');
    }

    public function getResolutionName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'resolution_name');
    }

}
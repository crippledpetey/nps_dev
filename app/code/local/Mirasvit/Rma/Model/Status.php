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


class Mirasvit_Rma_Model_Status extends Mage_Core_Model_Abstract
{
    const APPROVED = 'approved';
    const PACKAGE_SENT = 'package_sent';
    const REJECTED = 'rejected';
    const CLOSED = 'closed';

    protected function _construct()
    {
        $this->_init('rma/status');
    }

    public function toOptionArray($emptyOption = false)
    {
    	return $this->getCollection()->setOrder('sort_order', 'asc')->toOptionArray($emptyOption);
    }

    public function getName()
    {
        return Mage::helper('rma/storeview')->getStoreViewValue($this, 'name');
    }

    public function setName($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'name', $value);
        return $this;
    }

    public function getCustomerMessage()
    {
        return Mage::helper('rma/storeview')->getStoreViewValue($this, 'customer_message');
    }

    public function setCustomerMessage($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'customer_message', $value);
        return $this;
    }

    public function getHistoryMessage()
    {
        return Mage::helper('rma/storeview')->getStoreViewValue($this, 'history_message');
    }

    public function setHistoryMessage($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'history_message', $value);
        return $this;
    }

    public function getAdminMessage()
    {
        return Mage::helper('rma/storeview')->getStoreViewValue($this, 'admin_message');
    }

    public function setAdminMessage($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'admin_message', $value);
        return $this;
    }

    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        if (isset($data['customer_message']) && strpos($data['customer_message'], 'a:') !== 0) {
            $this->setCustomerMessage($data['customer_message']);
            unset($data['customer_message']);
        }

        if (isset($data['history_message']) && strpos($data['history_message'], 'a:') !== 0) {
            $this->setHistoryMessage($data['history_message']);
            unset($data['history_message']);
        }

        if (isset($data['admin_message']) && strpos($data['admin_message'], 'a:') !== 0) {
            $this->setAdminMessage($data['admin_message']);
            unset($data['admin_message']);
        }

        return parent::addData($data);
    }
	/************************/

    public function __toString()
    {
        return $this->getName();
    }
}
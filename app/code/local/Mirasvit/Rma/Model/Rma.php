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


class Mirasvit_Rma_Model_Rma extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('rma/rma');
    }

    public function toOptionArray($emptyOption = false)
    {
    	return $this->getCollection()->toOptionArray($emptyOption);
    }

    protected $_itemCollection;
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
            $this->_itemCollection = Mage::getModel('rma/item')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId())
                ->addFieldToFilter('qty_requested', array('gt' => 0));
        }
        return $this->_itemCollection;
    }

	protected $_commentCollection;
	public function getCommentCollection()
	{
		if (!$this->_commentCollection) {
			$this->_commentCollection = Mage::getModel('rma/comment')->getCollection()
				->addFieldToFilter('rma_id', $this->getRmaId());
		}
		return $this->_commentCollection;
	}

    protected $_order = null;
    public function getOrder()
    {
        if (!$this->getOrderId()) {
            return false;
        }
    	if ($this->_order === null) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
    	}
    	return $this->_order;
    }

    protected $_exchange_order = null;
    public function getExchangeOrder()
    {
        if (!$this->getExchangeOrderId()) {
            return false;
        }
    	if ($this->_exchange_order === null) {
            $this->_exchange_order = Mage::getModel('sales/order')->load($this->getExchangeOrderId());
    	}
    	return $this->_exchange_order;
    }

    protected $_store = null;
    public function getStore()
    {
        if (!$this->getStoreId()) {
            return false;
        }
    	if ($this->_store === null) {
            $this->_store = Mage::getModel('core/store')->load($this->getStoreId());
    	}
    	return $this->_store;
    }

    protected $_customer = null;
    public function getCustomer()
    {
    	if ($this->_customer === null) {
            if ($this->getCustomerId()) {
                $this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            } elseif ($this->getFirstname()) {
                $this->_customer = new Varien_Object(array(
                    'firstname' => $this->getFirstname(),
                    'lastname' => $this->getLastname(),
                    'name' => $this->getFirstname().' '.$this->getLastname(),
                    'email' => $this->getEmail(),
                ));
            } else {
                $this->_customer = false;
            }
    	}
    	return $this->_customer;
    }

    protected $_status = null;
    public function getStatus()
    {
        if (!$this->getStatusId()) {
            return false;
        }
    	if ($this->_status === null) {
            $this->_status = Mage::getModel('rma/status')->load($this->getStatusId());
    	}
    	return $this->_status;
    }

	/************************/


    protected $_ticket = null;
    public function getTicket()
    {
        if (!$this->getTicketId()) {
            return false;
        }
        if ($this->_ticket === null) {
            $this->_ticket = Mage::getModel('helpdesk/ticket')->load($this->getTicketId());
        }
        return $this->_ticket;
    }

    protected $_creditmemo_order = null;
    public function getCreditMemo()
    {
        if (!$this->getCreditMemoId()) {
            return false;
        }
        if ($this->_creditmemo_order === null) {
            $this->_creditmemo_order = Mage::getModel('sales/order_creditmemo')->load($this->getCreditMemoId());
        }
        return $this->_creditmemo_order;
    }


    public function getUrl()
    {
        return Mage::getUrl('rma/rma/view', array('id' => $this->getId()));
    }

    public function getGuestUrl() {
        $url = Mage::getUrl('rma/guest/view', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));
        return $url;
    }

    public function getPrintUrl() {
        $url = Mage::getUrl('rma/rma/print', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));
        return $url;
    }

    public function getGuestPrintUrl() {
        $url = Mage::getUrl('rma/guest/print', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));
        return $url;
    }

    public function getGuestPrintLabelUrl()
    {
        if (!$this->getReturnLabel()) {
            return false;
        }
        return Mage::getUrl('rma/guest/printlabel', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));
    }

    public function getBackendUrl() {
        $url = Mage::helper("adminhtml")->getUrl("rma/adminhtml_rma/edit", array('id'=>$this->getId()));
        return $url;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getGuestId()) {
            $this->setGuestId(md5($this->getId().Mage::helper('rma/string')->generateRandString(10)));
        }

        $config = Mage::getSingleton('rma/config');
        if (!$this->getId()) {
            $this->setIsAdminRead(true);
        }
        if (!$this->getStatusId()) {
            $this->setStatusId($config->getGeneralDefaultStatus());
        }
        if (!$this->getUserId()) {
            $this->setUserId($config->getGeneralDefaultUser());
        }
        if (!$this->getIsResolved()) {
            $status = $this->getStatus();
            if ($status->getIsRmaResolved()) {
                $this->setIsResolved(true);
            }
        }
    }

    public function getIsShowShippingBlock() {
        if (!$this->getStatus()) {
            return false;
        }
        return $this->getStatus()->getIsRmaResolved();
    }

    protected function _afterSaveCommit()
    {
        parent::_afterSaveCommit();
        if (!$this->getIncrementId()) {
            $this->setIncrementId(Mage::helper('rma')->generateIncrementId($this));
            $this->save();
        }
    }

    public function getShippingAddressHtml()
    {
        $items = array();
        $items[] = $this->getFirstname().' '.$this->getLastname();
        if ($this->getEmail()) {
            $items[] = $this->getEmail();
        }
        if ($this->getTelephone()) {
            $items[] = $this->getTelephone();
        }
        if ($this->getCompany()) {
            $items[] = $this->getCompany();
        }
        if ($this->getStreet()) {
            $items[] = $this->getStreet();
        }
        if ($this->getCity()) {
            $items[] = $this->getCity();
        }
        if ($this->getRegion()) {
            $items[] = $this->getRegion();
        }
        if ($this->getCountryId()) {
            $country = Mage::getModel('directory/country')->loadByCode($this->getCountryId());
            $items[] = $country->getName();
        }
        return implode('<br>', $items);
    }

    public function getReturnAddress()
    {
        return Mage::getSingleton('rma/config')->getGeneralReturnAddress($this->getStoreId());
    }

    public function getReturnAddressHtml()
    {
        return Mage::helper('rma')->convertToHtml($this->getReturnAddress());
    }

    public function addComment($text, $isHtml, $customer, $user, $isNotify, $isVisible, $isNotifyAdmin = true, $email = false)
    {
        $comment = Mage::getModel('rma/comment')
            ->setRmaId($this->getId())
            ->setText($text, $isHtml)
            ->setIsVisibleInFrontend($isVisible)
            ->setIsCustomerNotified($isNotify)
            ->save();


        if ($email) {
            $comment->setEmailId($email->getId());
            $email->setIsProcessed(true)
                  ->save();
            Mage::helper('rma')->copyEmailAttachments($email, $comment);
        } else {
            $allowedExtensions = Mage::helper('rma/attachment')->getAllowedExtensions();
            $allowedSize = Mage::helper('rma/attachment')->getAllowedSize()*1024*1024;

            Mage::helper('mstcore/attachment')->saveAttachments('COMMENT', $comment->getId(), 'attachment', $allowedExtensions, $allowedSize);
        }

        if ($customer) {
            $comment->setCustomerId($customer->getId())
                    ->setCustomerName($customer->getName());
            $this->setLastReplyName($customer->getName());
            $this->setIsAdminRead(false);
            if ($isNotifyAdmin) {
                Mage::helper('rma/mail')->sendNotificationAdminEmail($this, $comment);
            }
        } elseif ($user) {
            $comment->setUserId($user->getId());
            $this->setUserId($user->getId());
            $this->setLastReplyName($user->getName());
            $this->setIsAdminRead(true);
            if ($isNotify) {
                Mage::helper('rma/mail')->sendNotificationCustomerEmail($this, $comment);
            }
        }
        $comment->save();
        $this->save();

        return $comment;
    }

    public function initFromOrder($orderId)
    {
        $this->setOrderId($orderId);
        $order = $this->getOrder();

        $this->setCustomerId($order->getCustomerId());
        if ($customer = $this->getCustomer()) {
            $data = $customer->getData();
            unset($data['increment_id']);
            $this->addData($data);
        } else {
            $this->setEmail($order->getCustomerEmail());
        }

        $address = $order->getShippingAddress();
        if (!$address) {
            $address = $order->getBillingAddress();
        }
        $data = $address->getData();
        if (!$address->getEmail() || trim($address->getEmail()) == '')  {
            unset($data['email']);
        }
        unset($data['increment_id']);
        $this->addData($data);
        return $this;
    }

    public function getName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    public function getReturnLabel()
    {
        return Mage::helper('mstcore/attachment')->getAttachment('rma_return_label', $this->getId());
    }

    protected $_user = null;
    public function getUser()
    {
        if (!$this->getUserId()) {
            return false;
        }
        if ($this->_user === null) {
            $this->_user = Mage::getModel('admin/user')->load($this->getUserId());
        }
        return $this->_user;
    }

    public function getCode()
    {
        return 'RMA-'.$this->getGuestId();
    }

    public function getLastComment() {
        $collection = Mage::getModel('rma/comment')->getCollection()
            ->addFieldToFilter('rma_id', $this->getId())
            ->setOrder('comment_id', 'asc');
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }

    public function getUserName()
    {
        if ($this->getUser()) {
            return $this->getUser()->getName();
        } else {
            return Mage::helper('rma')->__('Unassigned');
        }
    }

    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAt(), $format).' '.Mage::helper('core')->formatTime($this->getCreatedAt(), $format);
    }

    public function getUpdatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getUpdatedAt(), $format).' '.Mage::helper('core')->formatTime($this->getUpdatedAt(), $format);
    }

    public function confirmShipping()
    {
        if ($status = Mage::helper('rma')->getStatusByCode(Mirasvit_Rma_Model_Status::PACKAGE_SENT)) {
            $this->setStatusId($status->getId());
            $this->save();
            Mage::helper('rma/process')->notifyRmaChange($this);
        }
    }

    public function getStatusName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'status_name');
    }
}
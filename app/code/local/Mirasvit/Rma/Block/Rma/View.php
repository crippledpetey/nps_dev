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



class Mirasvit_Rma_Block_Rma_View extends Mage_Core_Block_Template
{
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $rma = $this->getRma();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($rma && $headBlock) {
            $headBlock->setTitle(Mage::helper('rma')->__('RMA #%s', $rma->getIncrementId()));
        }
    }

    public function getId()
    {
        return $this->getRma()->getId();
    }

    public function getRma()
    {
        return Mage::registry('current_rma');
    }

    public function getOrderUrl($orderId)
    {
        return Mage::getUrl('sales/order/view', array('order_id' => $orderId));
    }

    public function getCommentPostUrl()
    {
        return Mage::getUrl('rma/rma/savecomment');
    }

    public function getListUrl()
    {
        return Mage::getUrl('rma/rma');
    }

    protected $commentCollection = false;
    public function getCommentCollection()
    {
        if (!$this->commentCollection) {
            $this->commentCollection = $this->getRma()->getCommentCollection()
                ->addFieldToFilter('is_visible_in_frontend', true)
                ;
        }
        return $this->commentCollection;
    }

    public function getConfirmationUrl()
    {
        return Mage::getUrl('rma/rma/savecomment', array('id' => $this->getRma()->getId(), 'shipping_confirmation' => true));
    }

    public function getPrintUrl()
    {
        return $this->getRma()->getGuestPrintUrl();
    }

    public function getPrintLabelUrl()
    {
        return $this->getRma()->getGuestPrintLabelUrl();
    }

    public function getCustomFields($isEdit = false)
    {
        $collection = Mage::helper('rma/field')->getVisibleCustomerCollection($this->getRma()->getStatusId(), $isEdit);
        return $collection;
    }

    public function getShippingConfirmationFields()
    {
        $collection = Mage::helper('rma/field')->getShippingConfirmationFields();
        return $collection;
    }

    public function getShippingConfirmation()
    {
        $str = $this->getConfig()->getGeneralShippingConfirmationText();
        $str = str_replace('"', '\'', $str);
        return $str;
    }

    public function getIsRequireShippingConfirmation()
    {
        $dontShowShippingConfirmationButton = array(
            Mirasvit_Rma_Model_Status::PACKAGE_SENT,
            Mirasvit_Rma_Model_Status::REJECTED,
            Mirasvit_Rma_Model_Status::CLOSED,
        );

        if (in_array($this->getRma()->getStatus()->getCode(), $dontShowShippingConfirmationButton)) {
            return false;
        }
        return $this->getConfig()->getGeneralIsRequireShippingConfirmation();
    }


}
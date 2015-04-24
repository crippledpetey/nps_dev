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


class Mirasvit_Rma_Block_Rma_List_List extends Mage_Core_Block_Template
{
    protected $_collection;
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $toolbar = $this->getLayout()->createBlock('rma/rma_list_toolbar', 'rma.toolbar')
            ->setTemplate('mst_rma/rma/list/toolbar.phtml')
            ->setAvailableListModes('list')
        ;
        $toolbar->setCollection($this->getRmaCollection());
        $this->append($toolbar);
    }

    public function getConfig() {
        return Mage::getSingleton('rma/config');
    }

    public function getRmaCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('rma/rma')->getCollection()
                ->addFieldToFilter('main_table.customer_id', $this->getCustomer()->getId())
                ->setOrder('created_at', 'desc');
            if ($order = $this->getOrder()) {
                $this->_collection->addFieldToFilter('order_id', $order->getId());
            }
        }
        return $this->_collection;
    }

    public function getOrder() {
        return Mage::registry('current_order');
    }

    protected function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

}
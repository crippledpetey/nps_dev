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


class Mirasvit_Rma_Block_Rma_Order extends Mirasvit_Rma_Block_Rma_List
{
    public function getOrder() {
        return Mage::registry('current_order');
    }

    public function isOrderPage() {
        return is_object($this->getOrder());
    }

    public function isReturnAllowed() {
        if ($order = $this->getOrder()) {
            return Mage::helper('rma')->isReturnAllowed($order);
        }
    }
}
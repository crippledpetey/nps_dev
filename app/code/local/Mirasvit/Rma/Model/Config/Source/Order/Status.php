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



class Mirasvit_Rma_Model_Config_Source_Order_Status
{

    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options = array();
			$statuses = Mage::getModel('sales/order_config')->getStatuses();
			foreach ($statuses as $id => $status) {
				$this->_options[] = array('value'=>$id, 'label'=>$status);
			}
        }
        return $this->_options;
    }

}

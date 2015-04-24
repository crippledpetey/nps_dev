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


class Mirasvit_Rma_Model_Config_Source_Rma_Grid_Columns
{

    public function toArray()
    {
        $options = array(
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_INCREMENT_ID => Mage::helper('rma')->__('RMA #'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_ORDER_INCREMENT_ID => Mage::helper('rma')->__('Order #'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_CUSTOMER_NAME => Mage::helper('rma')->__('Customer Name'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_USER_ID => Mage::helper('rma')->__('Owner'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_LAST_REPLY_NAME => Mage::helper('rma')->__('Last Replier'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_STATUS_ID => Mage::helper('rma')->__('Status'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_STORE_ID => Mage::helper('rma')->__('Store'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_CREATED_AT => Mage::helper('rma')->__('Created At'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_UPDATED_AT => Mage::helper('rma')->__('Last Activity'),
            Mirasvit_Rma_Model_Config::RMA_GRID_COLUMNS_ACTION => Mage::helper('rma')->__('View link'),
        );
        foreach (Mage::helper('rma/field')->getStaffCollection() as $field) {
            $options[$field->getCode()] = $field->getName();
        }
        return $options;
    }
    public function toOptionArray()
    {
        $result = array();
        foreach($this->toArray() as $k=>$v) {
            $result[] = array('value'=>$k, 'label'=>$v);
        }
        return $result;
    }

    /************************/
}
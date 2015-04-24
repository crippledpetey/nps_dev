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


class Mirasvit_Rma_Model_Config_Source_Is_Manual_Creditmemo
{

    public function toArray()
    {
        return array(
            Mirasvit_Rma_Model_Config::IS_MANUAL_CREDITMEMO_1 => Mage::helper('rma')->__('Manually'),
            Mirasvit_Rma_Model_Config::IS_MANUAL_CREDITMEMO_0 => Mage::helper('rma')->__('Automatically'),
            Mirasvit_Rma_Model_Config::IS_MANUAL_CREDITMEMO_2 => Mage::helper('rma')->__('Allow both ways'),
        );
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
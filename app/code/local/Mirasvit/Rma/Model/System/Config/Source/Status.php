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


class Mirasvit_Rma_Model_System_Config_Source_Status  extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    public function getAllOptions()
    {
        $options = array(
            array('value'=>'1', 'label'=> Mage::helper('rma')->__('Yes')),
            array('value'=>'0', 'label'=> Mage::helper('rma')->__('No'))
        );
        return $options;
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
	/************************/

}
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


class Mirasvit_Rma_Model_System_Config_Source_Admin_User {

    public function toArray()
    {
        $arr = Mage::getModel('admin/user')->getCollection()->toArray();
        $result = array();
        foreach ($arr['items'] as $value) {
            $result[$value['user_id']] = $value['firstname'].' '.$value['lastname'];
        }
        return $result;
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
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


class Mirasvit_Rma_Model_Reason extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('rma/reason');
    }

    public function toOptionArray($emptyOption = false)
    {
    	return $this->getCollection()->toOptionArray($emptyOption);
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

    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }
        return parent::addData($data);
    }
	/************************/

}
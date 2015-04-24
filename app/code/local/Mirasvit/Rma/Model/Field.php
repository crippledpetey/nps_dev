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


class Mirasvit_Rma_Model_Field extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('rma/field');
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

    public function getDescription()
    {
        return Mage::helper('rma/storeview')->getStoreViewValue($this, 'description');
    }

    public function setDescription($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'description', $value);
        return $this;
    }

    // public function getValues()
    // {
    //     return Mage::helper('rma/storeview')->getStoreViewValue($this, 'values');
    // }

    public function setValues($value)
    {
        Mage::helper('rma/storeview')->setStoreViewValue($this, 'values', $value);
        return $this;
    }

    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        if (isset($data['description']) && strpos($data['description'], 'a:') !== 0) {
            $this->setDescription($data['description']);
            unset($data['description']);
        }

        if (isset($data['values']) && strpos($data['values'], 'a:') !== 0) {
            $this->setValues($data['values']);
            unset($data['values']);
        }

        return parent::addData($data);
    }
	/************************/

	public function getValues($emptyOption = false)
    {
        $values = Mage::helper('rma/storeview')->getStoreViewValue($this, 'values');
    	$arr =  explode("\n", $values);
        $values = array();
        foreach ($arr as $value) {
            $value = explode('|', $value);
            if (count($value) >= 2) {
                $values[trim($value[0])] = trim($value[1]);
            }
        }
    	if ($emptyOption) {
            $res = array();
            $res[] = array('value'=>'', 'label'=> Mage::helper('rma')->__('-- Please Select --'));
            foreach ($values as $index => $value) {
                $res[] = array(
                   'value' => $index,
                   'label' => $value
                );
            }
            $values = $res;
        }
        return $values;
    }

    public function getVisibleCustomerStatus()
    {
        if (is_string($this->getData('visible_customer_status'))) {
            $this->setData('visible_customer_status', explode(",", $this->getData('visible_customer_status')));
        }

        return $this->getData('visible_customer_status');
    }

    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setIsNew(true);
        }
        return parent::_beforeSave();
    }

    protected function _afterSaveCommit()
    {
        parent::_afterSaveCommit();

        if ($this->getIsNew()) {
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $tableName = $resource->getTableName('rma/rma');
            $query = "ALTER TABLE `{$tableName}` ADD `{$this->getCode()}` TEXT";
            $writeConnection->query($query);
            $writeConnection->resetDdlCache();
        }
    }

    protected function _beforeDelete()
    {
        $field = Mage::getModel('rma/field')->load($this->getId());
        $this->setDbCode($field->getCode());
        return parent::_beforeDelete();
    }

    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName('rma/rma');
        $query = "ALTER TABLE `{$tableName}` DROP `{$this->getDbCode()}`";
        $writeConnection->query($query);
        $writeConnection->resetDdlCache();
    }
}
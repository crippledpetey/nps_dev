<?php

class Bc_Manufacturer_Model_Manufacturer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('manufacturer/manufacturer');
    }
}
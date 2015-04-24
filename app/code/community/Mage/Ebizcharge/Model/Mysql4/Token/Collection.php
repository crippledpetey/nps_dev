<?php

class Mage_Ebizcharge_Model_Mysql4_Token_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('ebizcharge/token');
    }

}

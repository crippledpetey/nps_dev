<?php

class Mage_Ebizcharge_Model_Mysql4_Token extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('ebizcharge/token', 'token_id');
    }

}

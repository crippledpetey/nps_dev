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


class Mirasvit_Rma_Helper_Validator extends Mirasvit_MstCore_Helper_Validator_Abstract
{
    public function testMagentoCrc()
    {
        $filter = array(
            'app/code/Mage/Core',
            'js'
        );
        return Mage::helper('mstcore/validator_crc')->testMagentoCrc($filter);
    }

    public function testMirasvitCrc()
    {
        $modules = array('Rma');
        return Mage::helper('mstcore/validator_crc')->testMirasvitCrc($modules);
    }

    public function testISpeedCache()
    {
        $result = self::SUCCESS;
        $title = 'My_Ispeed';
        $description = array();
        if (Mage::helper('mstcore')->isModuleInstalled('My_Ispeed')) {
            $result = self::INFO;
            $description[] = 'Extension My_Ispeed is installed. Please, go to the Configuration > Settings > I-Speed > General Configuration and add \'rma\' to the list of Ignored URLs. Then clear ALL cache.';
        }

        return array($result, $title, $description);
    }

    public function testMgtVarnishCache()
    {
        $result = self::SUCCESS;
        $title = 'Mgt_Varnish';
        $description = array();
        if (Mage::helper('mstcore')->isModuleInstalled('Mgt_Varnish')) {
            $result = self::INFO;
            $description[] = 'Extension Mgt_Varnish is installed. Please, go to the Configuration > Settings > MGT-COMMERCE.COM > Varnish and add \'rma\' to the list of Excluded Routes. Then clear ALL cache.';
        }

        return array($result, $title, $description);
    }


    public function testTables()
    {
        $tables = array(
            'admin/user',
            'core/store',
            'rma/comment',
            'rma/condition',
            'rma/field',
            'rma/item',
            'rma/reason',
            'rma/resolution',
            'rma/rma',
            'rma/rma_store',
            'rma/status',
            'rma/template',
            'rma/template_store',
        );
        return $this->dbCheckTables($tables);
    }
}
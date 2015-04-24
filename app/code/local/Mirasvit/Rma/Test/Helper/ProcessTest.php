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


if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Helpdesk')) {

class Mirasvit_Rma_Test_Model_ProcessTest extends EcomDev_PHPUnit_Test_Case
{
    protected $helper;
    protected function setUp()
    {
        $this->helper = Mage::helper('rma/process');
        $this->mockConfigMethod(array(
            'getNotificationAdminEmail' => 'notification@example.com',
            'isActiveHelpdesk' => 1,
        ));
        if (!Mage::registry('isSecureArea')) {
            Mage::register('isSecureArea', true);
        }
        //
            $this->markTestSkipped(
              'The Help Desk extension is not available.'
            );
        // }
    }

    protected function mockConfigMethod($methods)
    {
        $config = $this->getModelMock('rma/config', array_keys($methods));
        foreach ($methods as $method => $value) {
            $config->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }
        $this->replaceByMock('singleton', 'rma/config', $config);
    }

    /**
     * @test
     * @loadFixture data2
     */
    public function processEmailRegisteredCustomerTest()
    {
        $helper = Mage::helper('helpdesk/email');
        //mail from known customer
        $email = Mage::getModel('helpdesk/email')->load(2);

        $rma = $helper->processEmail($email);
        $this->assertEquals('Mirasvit_Rma_Model_Rma', get_class($rma));
        $this->assertEquals(2, $rma->getId());
        $comment = $rma->getLastComment();
        $this->assertEquals('John Doe', $comment->getCustomerName());
        $this->assertEquals(2, $comment->getCustomerId());
        $this->assertEquals(null, $comment->getUserId());
    }

    /**
     * @test
     * @loadFixture data2
     */
    public function processEmailUserTest()
    {
        $helper = Mage::helper('helpdesk/email');
        //mail from known customer
        $email = Mage::getModel('helpdesk/email')->load(3);

        $rma = $helper->processEmail($email);
        $this->assertEquals(2, $rma->getId());
        $comment = $rma->getLastComment();
        $this->assertEquals(null, $comment->getCustomerId());
        $this->assertEquals(2, $comment->getUserId());
    }

    /**
     * @test
     * @loadFixture data2
     */
    public function processEmailWithAttachments()
    {
        $helper = Mage::helper('helpdesk/email');
        //mail from known customer
        $email = Mage::getModel('helpdesk/email')->load(2);

        $rma = $helper->processEmail($email);
        $this->assertEquals(2, $rma->getId());
        $comment = $rma->getLastComment();
        $this->assertEquals(2, $email->getAttachments()->count());
        $this->assertEquals(2, $comment->getAttachments()->count());
    }
}

}

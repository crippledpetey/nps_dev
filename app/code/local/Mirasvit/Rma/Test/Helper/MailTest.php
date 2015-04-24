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



class Mirasvit_Rma_Helper_MailTest extends EcomDev_PHPUnit_Test_Case
{
    protected $helper;
    protected function getExpectedMail($code) {
        return file_get_contents(dirname(__FILE__)."/MailTest/expected/$code.html");
    }

    protected function setUp()
    {
        parent::setUp();
        $this->helper = Mage::helper('rma/mail');
        $this->helper->emails = array();
        Mage::helper('msttest/mock')->mockSingletonMethod('rma/config', array(
            'getNotificationSenderEmail' => 'general',
            'getNotificationAdminEmail' => 'admin_test@example.com',
             'getGeneralReturnAddress' => 'some return address',
        ));
    }

    // /**
    //  * @test
    //  * @loadFixture data
    //  */
    // public function sendNotificationCustomerEmailTest()
    // {
    //     $rma = Mage::getModel('rma/rma')->load(2);
    //     $comment = Mage::getModel('rma/comment')->load(2);
    //     $this->helper->sendNotificationCustomerEmail($rma, $comment);
    //     $result = Mage::helper('msttest/string')->html2txt($this->helper->emails[0]['text']);
    //    // echo $result;die;
    //     $this->assertEquals($this->getExpectedMail('notification_customer_email_template'), $result);
    //     $this->assertEquals('john_test@example.com', $this->helper->emails[0]['recipient_email']);
    //     $this->assertEquals('John Doe', $this->helper->emails[0]['recipient_name']);
    // }

    // /**
    //  * @test
    //  * @loadFixture data
    //  */
    // public function sendNotificationAdminEmailTest()
    // {
    //     $rma = Mage::getModel('rma/rma')->load(2);
    //     $comment = Mage::getModel('rma/comment')->load(2);
    //     $this->helper->sendNotificationAdminEmail($rma, $comment);
    //     $result = Mage::helper('msttest/string')->html2txt($this->helper->emails[0]['text']);
    //     // echo $result;die;
    //     $this->assertEquals($this->getExpectedMail('notification_admin_email_template'), $result);
    //     $this->assertEquals('admin_test@example.com', $this->helper->emails[0]['recipient_email']);
    //     $this->assertEquals('', $this->helper->emails[0]['recipient_name']);
    // }

    // *
    //  * @test
    //  * @loadFixture data2

    // public function sendNotificationUnregisteredCustomerEmailTest()
    // {
    //     $rma = Mage::getModel('rma/rma')->load(2);
    //     $comment = Mage::getModel('rma/comment')->load(2);
    //     $this->helper->sendNotificationCustomerEmail($rma, $comment);
    //     $result = Mage::helper('msttest/string')->html2txt($this->helper->emails[0]['text']);
    //     // echo $result;die;
    //     $this->assertEquals($this->getExpectedMail('notification_customer_email_template'), $result);
    //     $this->assertEquals('john_test@example.com', $this->helper->emails[0]['recipient_email']);
    //     $this->assertEquals('John Doe', $this->helper->emails[0]['recipient_name']);
    // }

    // /**
    //  * @test
    //  * @loadFixture data2
    //  */
    // public function sendNotificationUnregisteredAdminEmailTest()
    // {
    //     $rma = Mage::getModel('rma/rma')->load(2);
    //     $comment = Mage::getModel('rma/comment')->load(2);
    //     $this->helper->sendNotificationAdminEmail($rma, $comment);
    //     $result = Mage::helper('msttest/string')->html2txt($this->helper->emails[0]['text']);
    //     // echo $result;die;
    //     $this->assertEquals($this->getExpectedMail('notification_admin_email_template'), $result);
    //     $this->assertEquals('admin_test@example.com', $this->helper->emails[0]['recipient_email']);
    //     $this->assertEquals('', $this->helper->emails[0]['recipient_name']);
    // }


    /**
     * @test
     * @dataProvider parseVariablesProvider
     * @loadFixture data3
     */
    public function parseVariablesTest($expected, $inputMessage) {
        $rma = Mage::getModel('rma/rma')->load(2);
        $result = $this->helper->parseVariables($inputMessage, $rma);
        $result = Mage::helper('msttest/string')->html2txt($result);
        // echo $result;die;
        $this->assertEquals($expected, $result);
    }

    public function parseVariablesProvider()
    {
        return array(
            array("2", "{{var rma.id}}"),
            array("1000002", "{{var rma.increment_id}}"),
            array("1000032", "{{var order.increment_id}}"),
            array("John Doe", "{{var customer.name}}"),
            array("some return address", "{{var rma.return_address_html}}"),
            array("http://example.com/rma/guest/view/id/abcdef12345/", "{{var rma.guest_url}}"),
            array("http://example.com/rma/guest/print/id/abcdef12345/", "{{var rma.guest_print_url}}"),
            array(
"PRODUCT NAME
SKU
QTY
REASON
CONDITION
RESOLUTION

Example Product
example_product
2
Don't like
Opened
Refund", '{{block type="rma/rma_view_items" rma=$rma}}')
);
    }
}
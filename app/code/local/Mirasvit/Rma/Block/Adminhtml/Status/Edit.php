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


class Mirasvit_Rma_Block_Adminhtml_Status_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $statusConstant = array(
        Mirasvit_Rma_Model_Status::APPROVED,
        Mirasvit_Rma_Model_Status::PACKAGE_SENT,
        Mirasvit_Rma_Model_Status::REJECTED,
        Mirasvit_Rma_Model_Status::CLOSED
    );

    public function __construct ()
    {
        parent::__construct();
        $this->_objectId = 'status_id';
        $this->_controller = 'adminhtml_status';
        $this->_blockGroup = 'rma';


        $this->_updateButton('save', 'label', Mage::helper('rma')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('rma')->__('Delete'));


        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('rma')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action + 'back/edit/');
            }
        ";

        return $this;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function getStatus()
    {
        if (Mage::registry('current_status') && Mage::registry('current_status')->getId()) {
            if(in_array(Mage::registry('current_status')->getCode(), $this->statusConstant)) {
                $this->_updateButton('delete', 'disabled', 'true');
            }
            return Mage::registry('current_status');
        }
    }

    public function getHeaderText ()
    {
        if ($status = $this->getStatus()) {
            return Mage::helper('rma')->__("Edit Status '%s'", $this->htmlEscape($status->getName()));
        } else {
            return Mage::helper('rma')->__('Create New Status');
        }
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        $switcher = $this->getLayout()->createBlock('adminhtml/store_switcher');
        $switcher->setUseConfirm(false)->setSwitchUrl(
            $this->getUrl('*/*/*/', array('store' => null, '_current' => true))
        );
        $html = $switcher->toHtml().$html;
        return $html;
    }

    /************************/

}
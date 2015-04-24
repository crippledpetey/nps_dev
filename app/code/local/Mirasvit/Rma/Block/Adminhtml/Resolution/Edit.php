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


class Mirasvit_Rma_Block_Adminhtml_Resolution_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $resolutionConstant = array(
        Mirasvit_Rma_Model_Resolution::REFUND,
        Mirasvit_Rma_Model_Resolution::EXCHANGE
    );

    public function __construct ()
    {
        parent::__construct();
        $this->_objectId = 'resolution_id';
        $this->_controller = 'adminhtml_resolution';
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

    public function getResolution()
    {
        if (Mage::registry('current_resolution') && Mage::registry('current_resolution')->getId()) {
            if(in_array(Mage::registry('current_resolution')->getCode(), $this->resolutionConstant)) {
                $this->_updateButton('delete', 'disabled', 'true');
            }
            return Mage::registry('current_resolution');
        }
    }

    public function getHeaderText ()
    {
        if ($resolution = $this->getResolution()) {
            return Mage::helper('rma')->__("Edit Resolution '%s'", $this->htmlEscape($resolution->getName()));
        } else {
            return Mage::helper('rma')->__('Create New Resolution');
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
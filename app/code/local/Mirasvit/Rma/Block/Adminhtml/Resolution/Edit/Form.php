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


class Mirasvit_Rma_Block_Adminhtml_Resolution_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'),'store' => (int)$this->getRequest()->getParam('store'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $resolution = Mage::registry('current_resolution');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend'=> Mage::helper('rma')->__('General Information')));
        if ($resolution->getId()) {
            $fieldset->addField('resolution_id', 'hidden', array(
                'name'      => 'resolution_id',
                'value'     => $resolution->getId(),
            ));
        }
        $fieldset->addField('store_id', 'hidden', array(
            'name'  => 'store_id',
            'value' => (int)$this->getRequest()->getParam('store')
        ));

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('rma')->__('Title'),
            'name'      => 'name',
            'value'     => $resolution->getName(),
            'after_element_html' => ' [STORE VIEW]',
        ));
        $fieldset->addField('code', 'text', array(
            'label'     => Mage::helper('rma')->__('Code'),
            'required'  => true,
            'name'      => 'code',
            'value'     => $resolution->getCode(),
            'disabled'  => $resolution->getCode() == ''? '': 'disabled',
        ));
        $fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('rma')->__('Sort Order'),
            'name'      => 'sort_order',
            'value'     => $resolution->getSortOrder(),
        ));
        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('rma')->__('Is Active'),
            'name'      => 'is_active',
            'value'     => $resolution->getIsActive(),
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /************************/

}
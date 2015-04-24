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


class Mirasvit_Rma_Block_Adminhtml_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $template = Mage::registry('current_template');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend'=> Mage::helper('rma')->__('General Information')));
        if ($template->getId()) {
            $fieldset->addField('template_id', 'hidden', array(
                'name'      => 'template_id',
                'value'     => $template->getId(),
            ));
        }
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('rma')->__('Internal Title'),
            'name'      => 'name',
            'value'     => $template->getName(),
        ));
        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('rma')->__('Is Active'),
            'name'      => 'is_active',
            'value'     => $template->getIsActive(),
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
        ));
        $fieldset->addField('template', 'textarea', array(
            'label'     => Mage::helper('rma')->__('Template'),
            'name'      => 'template',
            'value'     => $template->getTemplate(),
            'note'      => Mage::helper('rma')->__('You can use variables: [rma_increment_id], [rma_firstname], [rma_lastname], [rma_email], [store_name], [store_phone], [store_address], [user_firstname], [user_lastname], [user_email]'),
        ));
        $fieldset->addField('store_ids', 'multiselect', array(
            'label'     => Mage::helper('rma')->__('Stores'),
            'required'  => true,
            'name'      => 'store_ids[]',
            'value'     => $template->getStoreIds(),
            'values'    => Mage::getModel('core/store')->getCollection()->toOptionArray()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /************************/

}
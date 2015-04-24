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


require 'app/code/local/Mirasvit/Rma/Block/Adminhtml/Rma/Edit/Renderer/Mfile.php';

class Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mst_rma/rma/edit/form.phtml');
    }

    public function getRma()
    {
        return Mage::registry('current_rma');
    }

    public function getOrderItemCollection()
    {
        $rma = $this->getRma();
        $order = $rma->getOrder();
        return $order->getItemsCollection();
    }

    public function getGeneralInfoForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend'=> Mage::helper('rma')->__('General Information')));
        if ($rma->getId()) {
            $fieldset->addField('rma_id', 'hidden', array(
                'name'      => 'rma_id',
                'value'     => $rma->getId(),
            ));
        }
        if ($rma->getTicketId()) {
            $fieldset->addField('ticket_id', 'hidden', array(
                'name'      => 'ticket_id',
                'value'     => $rma->getTicketId(),
            ));
        }

        $element = $fieldset->addField('increment_id', 'text', array(
            'label'     => Mage::helper('rma')->__('RMA #'),
            'name'      => 'increment_id',
            'value'     => $rma->getIncrementId(),
        ));

        if (!$rma->getId()) {
            $element->setNote('will be generated automatically, if empty');
        }
        $fieldset->addField('order_id', 'link', array(
            'label'     => Mage::helper('rma')->__('Order #'),
            'name'      => 'order_id',
            'value'     => '#'.$rma->getOrder()->getIncrementId(),
            'href'      => $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$rma->getOrderId()))
        ));
        if ($rma->getCustomerId()) {
            $fieldset->addField('customer', 'link', array(
                'label'     => Mage::helper('rma')->__('Customer'),
                'name'      => 'customer',
                'value'     => $rma->getName(),
                'href'      => Mage::helper('rma/mage')->getBackendCustomerUrl($rma->getCustomerId())
            ));
        } else {
            $fieldset->addField('customer', 'label', array(
                'label'     => Mage::helper('rma')->__('Customer'),
                'name'      => 'customer',
                'value'     => $rma->getName() . ($rma->getIsGift()? ' '.Mage::helper('rma')->__('(received a gift)'):''),
            ));
        }
        if ($ticket = $rma->getTicket()) {
            $fieldset->addField('ticket', 'link', array(
                'label'     => Mage::helper('rma')->__('Created From Ticket'),
                'name'      => 'ticket',
                'value'     => '#'.$ticket->getCode(),
                'href'      => $ticket->getBackendUrl()
            ));
        }
        $fieldset->addField('user_id', 'select', array(
            'label'     => Mage::helper('rma')->__('RMA Owner'),
            'name'      => 'user_id',
            'value'     => $rma->getUserId(),
            'values'    => Mage::helper('rma')->toAdminUserOptionArray(true)
        ));

        $fieldset->addField('status_id', 'select', array(
            'label'     => Mage::helper('rma')->__('Status'),
            'name'      => 'status_id',
            'value'     => $rma->getStatusId(),
            'values'    => Mage::getModel('rma/status')->getCollection()->setOrder('sort_order', 'asc')->toOptionArray()
        ));
        $fieldset->addField('return_label', 'mfile', array(
            'label'     => Mage::helper('rma')->__('Upload Return Label'),
            'name'      => 'return_label',
            'attachment'     => $rma->getReturnLabel(),
        ));

        if ($rma->getId()) {
            $fieldset->addField('guest_link', 'link', array(
                'label'    => Mage::helper('rma')->__('External Link'),
                'name'     => 'guest_link',
                'class'    => 'guest-link',
                'value'    => Mage::helper('rma')->__('open'),
                'href'    => $rma->getGuestUrl(),
                'target'    => '_blank',
            ));
        }
        if ($rma->getExchangeOrderId()) {
            $fieldset->addField('exchange_order_id', 'link', array(
                'label'     => Mage::helper('rma')->__('Exchange Order #'),
                'name'      => 'exchange_order_id',
                'value'     => '#'.$rma->getExchangeOrder()->getIncrementId(),
                'href'      => $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$rma->getExchangeOrderId()))
            ));
        }
        if ($rma->getCreditMemoId()) {
            $fieldset->addField('credit_memo_id', 'link', array(
                'label'     => Mage::helper('rma')->__('Credit Memo #'),
                'name'      => 'credit_memo_id',
                'value'     => '#'.$rma->getCreditMemo()->getIncrementId(),
                'href'      => $this->getUrl('adminhtml/sales_creditmemo/view', array('creditmemo_id'=>$rma->getCreditMemoId()))
            ));
        }
        return $form;
    }

    public function getFieldForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');
        $fieldset = $form->addFieldset('field_fieldset', array('legend'=> Mage::helper('rma')->__('Additional Information')));
        $collection = Mage::helper('rma/field')->getStaffCollection();
        if (!$collection->count()) {
            return false;
        }
        foreach ($collection as $field) {
            $fieldset->addField($field->getCode(), $field->getType(), Mage::helper('rma/field')->getInputParams($field, true, $rma));
        }
        return $form;
    }


    public function getTemplateForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');
        $collection = Mage::getModel('rma/template')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setOrder('name', 'asc');
        if ($rma->getId()) {
            $collection->addStoreFilter($rma->getStoreId());
        }

        if ($collection->count()) {
            if ($rma->getId()) {
                $options = $collection->toOptionArray(true);
            } else {
                $options = $collection->toOptionGroupArray(true);
            }
            $element = $form->addField('template_id', 'select', array(
                'label'     => Mage::helper('rma')->__('Insert Quick Response'),
                'name'      => 'template_id',
                'value'     => $rma->getTemplateId(),
                'values'    => $options
            ));

            $values = array();
            foreach ($collection as $template) {
                $text = trim($template->getParsedTemplate($rma));
                $values[] = "<div id='htmltemplate-{$template->getId()}' style='display:none;'>{$text}</div>";
            }
            $element->setAfterElementHtml(implode("\n", $values));
        }
        return $form;
    }


    public function getShippingAddressForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');

        $fieldset = $form->addFieldset('customer_fieldset', array('legend'=> Mage::helper('rma')->__('Contact Information')));
        $fieldset->addField('firstname', 'text', array(
            'label'     => Mage::helper('rma')->__('First Name'),
            'name'      => 'firstname',
            'value'     => $rma->getFirstname(),
        ));
        $fieldset->addField('lastname', 'text', array(
            'label'     => Mage::helper('rma')->__('Last Name'),
            'name'      => 'lastname',
            'value'     => $rma->getLastname(),
        ));
        $fieldset->addField('company', 'text', array(
            'label'     => Mage::helper('rma')->__('Company'),
            'name'      => 'company',
            'value'     => $rma->getCompany(),
        ));
        $fieldset->addField('telephone', 'text', array(
            'label'     => Mage::helper('rma')->__('Telephone'),
            'name'      => 'telephone',
            'value'     => $rma->getTelephone(),
        ));
        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('rma')->__('Email'),
            'name'      => 'email',
            'value'     => $rma->getEmail(),
        ));
        $street = explode("\n", $rma->getStreet());
        $fieldset->addField('street', 'hidden', array(
            'label'     => Mage::helper('rma')->__('Street Address'),
            'name'      => 'street',
            'value'     => $street[0],
        ));
        $fieldset->addField('street2', 'hidden', array(
            // 'label'     => Mage::helper('rma')->__('Street Address'),
            'name'      => 'street2',
            'value'     => isset($street[1])? $street[1]: '',
        ));
        $fieldset->addField('city', 'hidden', array(
            'label'     => Mage::helper('rma')->__('City'),
            'name'      => 'city',
            'value'     => $rma->getCity(),
        ));
        $fieldset->addField('postcode', 'hidden', array(
            'label'     => Mage::helper('rma')->__('Zip/Postcode'),
            'name'      => 'postcode',
            'value'     => $rma->getPostcode(),
        ));
        return $form;
    }


    public function getExchangeOrderForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');

        $fieldset = $form->addFieldset('customer_fieldset', array('legend'=> Mage::helper('rma')->__('Exchange Order Information')));
        $fieldset->addField('payment_method', 'select', array(
            'label'     => Mage::helper('rma')->__('Payment Method'),
            'name'      => 'payment_method',
        ));
        $fieldset->addField('shipping_method', 'select', array(
            'label'     => Mage::helper('rma')->__('Shipping Method'),
            'name'      => 'shipping_method',
        ));
        $fieldset->addField('shipping_cost', 'text', array(
            'label'     => Mage::helper('rma')->__('Shipping Cost'),
            'name'      => 'shipping_cost',
            'value'     => 0
        ));
        return $form;
    }


    public function getHistoryHtml()
    {
        $historyBlock = $this->getLayout()->createBlock('rma/adminhtml_rma_edit_form_history', 'rma_history');
        return $historyBlock->toHtml();
    }

    public function getReturnAddressHtml()
    {
        $address =  $this->getRma()->getReturnAddressHtml();
        return $address;
    }

    public function getReasonCollection()
    {
        return Mage::getModel('rma/reason')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    public function getResolutionCollection()
    {
        return Mage::getModel('rma/resolution')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    public function getConditionCollection()
    {
        return Mage::getModel('rma/condition')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }
    /************************/


    public function getMarkUrl($isRead)
    {
        return $this->getUrl('*/*/markRead', array('rma_id' => $this->getRma()->getId(), 'is_read' => (int)$isRead));
    }
}
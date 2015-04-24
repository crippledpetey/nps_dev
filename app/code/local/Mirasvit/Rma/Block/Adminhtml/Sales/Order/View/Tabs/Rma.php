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



class Mirasvit_Rma_Block_Adminhtml_Sales_Order_View_Tabs_Rma extends Mage_Adminhtml_Block_Widget
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return Mage::helper('rma')->__('RMA');
    }

    public function getTabTitle()
    {
        return Mage::helper('rma')->__('RMA');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _toHtml()
    {
        $id = $this->getRequest()->getParam('order_id');
        $rmaNewUrl = $this->getUrl('rmaadmin/adminhtml_rma/add', array('order_id' => $id));
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\'' . $rmaNewUrl . '\'')
            ->setLabel($this->__('Create RMA for this order'));


        $grid = $this->getLayout()->createBlock('rma/adminhtml_rma_grid');
        $grid->addCustomFilter('order_id', $id);
        $grid->setFilterVisibility(false);
        $grid->setExportVisibility(false);
        $grid->setPagerVisibility(0);

        $grid->setTabMode(true);

        if (Mage::helper('rma')->isReturnAllowed($id)) {
            $meetMessage = $this->__('Order meets RMA policy');
        } else {
            $meetMessage = $this->__('Order doesn\'t meet RMA policy');
        }

        return '<br>
        <div>' . $button->toHtml() . '<div style="float:right;color:#eb5e00"><i>'.$meetMessage.'</i></div>
        <br><br>'. $grid->toHtml().'</div>' ;

        // return '<div class="content-buttons-placeholder" style="height:25px;">' .
        // '<p class="content-buttons form-buttons" >' . $button->toHtml() . '</p>' .
        // '</div>' . $grid->toHtml();
    }
}
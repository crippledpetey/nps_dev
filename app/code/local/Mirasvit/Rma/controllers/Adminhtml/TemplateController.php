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


class Mirasvit_Rma_Adminhtml_TemplateController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction ()
    {
        $this->loadLayout()->_setActiveMenu('rma');

        return $this;
    }

    public function indexAction ()
    {
        $this->_title($this->__('Quick Responses'));
        $this->_initAction();
        $this->_addContent($this->getLayout()
            ->createBlock('rma/adminhtml_template'));
        $this->renderLayout();
    }

    public function addAction ()
    {
        $this->_title($this->__('New Template'));

        $this->_initTemplate();

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Template  Manager'),
                Mage::helper('adminhtml')->__('Template Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Template '), Mage::helper('adminhtml')->__('Add Template'));

        $this->getLayout()
            ->getBlock('head')
            ->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_template_edit'));
        $this->renderLayout();
    }

    public function editAction ()
    {
        $template = $this->_initTemplate();

        if ($template->getId()) {
            $this->_title($this->__("Edit Template '%s'", $template->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Quick Responses'),
                    Mage::helper('adminhtml')->__('Quick Responses'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Template '),
                    Mage::helper('adminhtml')->__('Edit Template '));

            $this->getLayout()
                ->getBlock('head')
                ->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rma/adminhtml_template_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The Template does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction ()
    {
        if ($data = $this->getRequest()->getPost()) {

            $template = $this->_initTemplate();
            $template->addData($data);
            //format date to standart
            // $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            // Mage::helper('mstcore/date')->formatDateForSave($template, 'active_from', $format);
            // Mage::helper('mstcore/date')->formatDateForSave($template, 'active_to', $format);

            try {
                $template->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Template was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $template->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find Template to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction ()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $template = Mage::getModel('rma/template');

                $template->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Template was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                    ->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massChangeAction()
    {
        $ids = $this->getRequest()->getParam('template_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Template(s)'));
        } else {
            try {
                $isActive = $this->getRequest()->getParam('is_active');
                foreach ($ids as $id) {
                    $template = Mage::getModel('rma/template')->load($id);
                    $template->setIsActive($isActive);
                    $template->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully updated', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('template_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Template(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $template = Mage::getModel('rma/template')
                        ->setIsMassDelete(true)
                        ->load($id);
                    $template->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function _initTemplate()
    {
        $template = Mage::getModel('rma/template');
        if ($this->getRequest()->getParam('id')) {
            $template->load($this->getRequest()->getParam('id'));
        }

        Mage::register('current_template', $template);

        return $template;
    }

    /************************/

}
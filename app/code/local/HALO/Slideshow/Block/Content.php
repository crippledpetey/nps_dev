<?php
class HALO_Slideshow_Block_Content extends Mage_Adminhtml_Block_Media_Uploader
{
//protected $_config;

    public function __construct()
    {
        parent::__construct();
    	$this->setUseAjax(true);
        $this->setId($this->getId() . '_Uploader');
        $this->setTemplate('halo_slideshow/uploader.phtml');
        $this->getConfig()->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/upload'));
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField('file');
        $this->getConfig()->setFilters(array(
            'media' => array(
                'label' => Mage::helper('adminhtml')->__('Images (.jpg, .jpeg, .png, .gif)'),
                'files' => array('*.jpg', '*.jpeg', '*.png','*.gif')
            )
        ));
    }
 public function getContentsUrl()
    {
        return $this->getUrl('*/admin_chooser/contents', array('type' => $this->getRequest()->getParam('type')));
    }
 
}
?>
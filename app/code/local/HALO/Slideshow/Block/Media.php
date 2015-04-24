<?php
class HALO_Slideshow_Block_Media  extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($arguments=array())
    {
       parent::__construct($arguments);
	   $this->setTemplate('halo_slideshow/grid.phtml');
        $this->setUseAjax(true);
    }
    protected $_filesCollection;

    public function getFiles()
    {
		$path =Mage::getBaseDir('media').DS.'halo_slideshow';
		if(file_exists($path))
		{
				if (! $this->_filesCollection) {
					$this->_filesCollection = Mage::getSingleton('cms/wysiwyg_images_storage')->getFilesCollection(Mage::getBaseDir('media') . DS . 'halo_slideshow','image');
				}
		}
        return $this->_filesCollection;
    }
      public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	 $pagesGrid = $this->getLayout()->createBlock('slideshow/media', '', array(
            'id' => $uniqId,
        ));
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('slideshow/admin_chooser/chooser', array('uniq_id' => $uniqId));

        $chooser = $this->getLayout()->createBlock('slideshow/widget')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);
        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }   
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var pageTitle = trElement.down("td").innerHTML;
                var pageId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                '.$chooserJsObject.'.setElementValue(pageId);
                '.$chooserJsObject.'.setElementLabel(pageTitle);
                '.$chooserJsObject.'.close();
            }
        ';
        return $js;
    }
    
    protected function _prepareCollection()
    {
        $collection = $this->getFiles();
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('chooser_id', array(
            'header'    => Mage::helper('slideshow')->__('File Base Name'),
            'align'     => 'left',
            'index'     => 'basename',
        ));

        $this->addColumn('chooser_title', array(
            'header'    => Mage::helper('slideshow')->__('File Name'),
            'align'     => 'left',
            'index'     => 'filename',
        ));
    }
    public function getGridUrl()
    {
        return $this->getUrl('slideshow/admin_chooser/chooser', array('_current' => true));
    }
    
    
}
?>
<?php
class Bc_Manufacturer_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/manufacturer?id=15 
    	 *  or
    	 * http://site.com/manufacturer/id/15 	
    	 */
    	/* 
		$manufacturer_id = $this->getRequest()->getParam('id');

  		if($manufacturer_id != null && $manufacturer_id != '')	{
			$manufacturer = Mage::getModel('manufacturer/manufacturer')->load($manufacturer_id)->getData();
		} else {
			$manufacturer = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($manufacturer == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$manufacturerTable = $resource->getTableName('manufacturer');
			
			$select = $read->select()
			   ->from($manufacturerTable,array('manufacturer_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$manufacturer = $read->fetchRow($select);
		}
		Mage::register('manufacturer', $manufacturer);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}
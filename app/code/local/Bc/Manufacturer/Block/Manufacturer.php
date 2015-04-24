<?php
class Bc_Manufacturer_Block_Manufacturer extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getManufacturer()     
     { 
        if (!$this->hasData('manufacturer')) {
            $this->setData('manufacturer', Mage::registry('manufacturer'));
        }
        return $this->getData('manufacturer');
        
    }
}
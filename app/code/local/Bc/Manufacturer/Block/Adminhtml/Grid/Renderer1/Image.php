<?php

class Bc_Manufacturer_Block_Adminhtml_Grid_Renderer1_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getdata('filename')==""){
            return "";
        }
        else{
            //you can also use some resizer here...
            return "<img src='".Mage::getBaseUrl("media")."/Manufacturer/".$row->getdata('filename')."' width='75' height='75'/>";
        }
    }
}
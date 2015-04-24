<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form text element
 *
 * @category   Varien
 * @package    Varien_Data
 * @author     Halothemes <halothemes.com>
 */
class Varien_Data_Form_Element_Textslideshow extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('textslideshow');
        $this->setExtType('text');
    }
	
	public function getHtml()
    {
        $this->addClass('input-text');
        return parent::getHtml();
    }
	
	public function getElementHtml()
    {
        $html = '<input id="'.$this->getHtmlId().'" name="'.$this->getName()
             .'" value="'.$this->getEscapedValue().'" type="text" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }
	
	public function getHtmlAttributes()
    {
        return array('title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex');
    }
	
	public function getEscapedValue($index=null)
    {
		$helper = Mage::helper('slideshow');
        $value = $helper->html_decode($this->getValue($index));

        if ($filter = $this->getValueFilter()) {
            $value = $filter->filter($value);
        }
        return $this->_escape($value);
    }
}

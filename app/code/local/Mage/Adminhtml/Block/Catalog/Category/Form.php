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
 * @category    NPS
 * @package     NPS_CategorySeo
 * @copyright   Copyright (c) 2015 Need Plumbing Supplies (http://needplumbingsupplies.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category tabs - NPS Category SEO
 *
 * @category   NPS
 * @package    NPS_CategorySeo
 * @author     NPS- Brandon Thomas (brandon@needplumbingsupplies.com)
 */

class NPS_CategorySeo_Block_Adminhtml_Category_Form extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('custom_category_tab_form', array('legend' => Mage::helper('catalog')->__('Custom Tab')));
		$fieldset->addField('anytext', 'text', array('label' => Mage::helper('catalog')->__('Any Text'), 'name' => 'anytext'));
		return parent::_prepareForm();
	}
}
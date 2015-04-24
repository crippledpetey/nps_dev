<?php
class HALO_Slideshow_Block_Widget extends Mage_Widget_Block_Adminhtml_Widget_Chooser
{
	
    protected function _toHtml()
    {
        $element   = $this->getElement();
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset  = $element->getForm()->getElement($this->getFieldsetId());
        $chooserId = $this->getUniqId();
        $config    = $this->getConfig();

        // add chooser element to fieldset
        $chooser = $fieldset->addField('chooser' . $element->getId(), 'note', array(
            'label'       => $config->getLabel() ? $config->getLabel() : '',
            'value_class' => 'value2',
        ));
        $hiddenHtml = '';
        if ($this->getHiddenEnabled()) {
            $hidden = new Varien_Data_Form_Element_Hidden($element->getData());
            $hidden->setId("{$chooserId}value")->setForm($element->getForm());
            if ($element->getRequired()) {
                $hidden->addClass('required-entry');
            }
            $hiddenHtml = $hidden->getElementHtml();
            $element->setValue('');
        }

        $buttons = $config->getButtons();
        $chooseButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setId($chooserId . 'control')
            ->setClass('btn-chooser')
            ->setLabel($buttons['open'])
            ->setOnclick($chooserId.'.choose()');
		$delButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setId($chooserId . 'del_control')
            ->setClass('btn-del-img')
            ->setLabel(' Delete image ') 
            ->setOnclick('del(this)');	
        $chooser->setData('after_element_html', $hiddenHtml . $chooseButton->toHtml().$delButton->toHtml());

        // render label and chooser scripts
        $configJson = Mage::helper('core')->jsonEncode($config->getData());
        return '
            <label class="widget-option-label" id="'.$chooserId . 'label">'.($this->getLabel() ? $this->getLabel() : Mage::helper('widget')->__('Not Selected')).'</label>
            <div id="'.$chooserId . 'advice-container" class="hidden"></div>
            <script type="text/javascript">
                '.$chooserId.' = new WysiwygWidget.chooser("'.$chooserId.'", "'.$this->getSourceUrl().'", '.$configJson.');
            </script>
        ';
    }
}
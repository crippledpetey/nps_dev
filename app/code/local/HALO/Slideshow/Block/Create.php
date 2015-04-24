<?php
class HALO_Slideshow_Block_Create extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    public function _construct() {
        parent::_construct();

        // default template location
    }
	
    protected function _toHtml() {
		$helper = Mage::helper('slideshow');
        
		$text1 = $helper->html_decode(trim($this->getData('text1')));
		$text2 = $helper->html_decode(trim($this->getData('text2')));
		$text3 = $helper->html_decode(trim($this->getData('text3')));
		$text4 = $helper->html_decode(trim($this->getData('text4')));
		$text5 = $helper->html_decode(trim($this->getData('text5')));
		$text6 = $helper->html_decode(trim($this->getData('text6')));
		$text7 = $helper->html_decode(trim($this->getData('text7')));
		$text8 = $helper->html_decode(trim($this->getData('text8')));
		$text9 = $helper->html_decode(trim($this->getData('text9')));
        
		$url1 = $helper->html_decode(trim($this->getData('url1')));
		$url2 = $helper->html_decode(trim($this->getData('url2')));
		$url3 = $helper->html_decode(trim($this->getData('url3')));
		$url4 = $helper->html_decode(trim($this->getData('url4')));
		$url5 = $helper->html_decode(trim($this->getData('url5')));
		$url6 = $helper->html_decode(trim($this->getData('url6')));
		$url7 = $helper->html_decode(trim($this->getData('url7')));
		$url8 = $helper->html_decode(trim($this->getData('url8')));
		$url9 = $helper->html_decode(trim($this->getData('url9')));
		
        $thumbsimage1 = $this->_urlimage(trim($this->getData('thumbsimage1')));
		$thumbsimage2 = $this->_urlimage(trim($this->getData('thumbsimage2')));
		$thumbsimage3 = $this->_urlimage(trim($this->getData('thumbsimage3')));
		$thumbsimage4 = $this->_urlimage(trim($this->getData('thumbsimage4')));
		$thumbsimage5 = $this->_urlimage(trim($this->getData('thumbsimage5')));
		$thumbsimage6 = $this->_urlimage(trim($this->getData('thumbsimage6')));
		$thumbsimage7 = $this->_urlimage(trim($this->getData('thumbsimage7')));
		$thumbsimage8 = $this->_urlimage(trim($this->getData('thumbsimage8')));
		$thumbsimage9 = $this->_urlimage(trim($this->getData('thumbsimage9')));
        
		$image1 = $this->_urlimage(trim($this->getData('image1')));
		$image2 = $this->_urlimage(trim($this->getData('image2')));
		$image3 = $this->_urlimage(trim($this->getData('image3')));
		$image4 = $this->_urlimage(trim($this->getData('image4')));
		$image5 = $this->_urlimage(trim($this->getData('image5')));
		$image6 = $this->_urlimage(trim($this->getData('image6')));
		$image7 = $this->_urlimage(trim($this->getData('image7')));
		$image8 = $this->_urlimage(trim($this->getData('image8')));
		$image9 = $this->_urlimage(trim($this->getData('image9')));
        	
        $effect = trim($this->getData('effect'));
        $slices = trim($this->getData('slices'));
        $boxcols = trim($this->getData('boxcols'));
        $boxrows = trim($this->getData('boxrows'));
		$animspeed = trim($this->getData('animspeed'));
		$pausetime = trim($this->getData('pausetime'));
		$startslide = trim($this->getData('startslide'));
		$directionnav = trim($this->getData('directionnav'));
		$controlnav = trim($this->getData('controlnav'));
		$controlnavthumbs = trim($this->getData('controlnavthumbs'));
		$pauseonhover = trim($this->getData('pauseonhover'));
		$manualadvance = trim($this->getData('manualadvance'));
		$prevtext = trim($this->getData('prevtext'));
		$nexttext = trim($this->getData('nexttext'));
		$randomstart = trim($this->getData('randomstart'));
        
		if ($slices == "") {
			$slices = "15";
		}
		if ($boxcols == "") {
			$boxcols = "8";
		}
		if ($boxrows == "") {
			$boxrows = "4";
		}
		if ($animspeed == "") {
			$animspeed = "500";
		}
    	if ($pausetime == "") {
			$pausetime = "5000";
		}
    	if ($startslide == "") {
			$startslide = "0";
		}
   		if ($prevtext == "") {
			$prevtext = "Prev";
		}		
		if ($nexttext == "") {
			$nexttext = "Next";
		}	
        $this->setTemplate('halo_slideshow/slideshow.phtml');
		$this->assign('text1', $text1);
		$this->assign('text2', $text2);
		$this->assign('text3', $text3);
		$this->assign('text4', $text4);
		$this->assign('text5', $text5);
		$this->assign('text6', $text6);
 		$this->assign('text7', $text7);
		$this->assign('text8', $text8);
		$this->assign('text9', $text9);
        
		$this->assign('url1', $url1);
		$this->assign('url2', $url2);
		$this->assign('url3', $url3);
		$this->assign('url4', $url4);
		$this->assign('url5', $url5);
		$this->assign('url6', $url6);
 		$this->assign('url7', $url7);
		$this->assign('url8', $url8);
		$this->assign('url9', $url9);
        
        $this->assign('thumbsimage1', $thumbsimage1);
		$this->assign('thumbsimage2', $thumbsimage2);
		$this->assign('thumbsimage3', $thumbsimage3);
		$this->assign('thumbsimage4', $thumbsimage4);
		$this->assign('thumbsimage5', $thumbsimage5);
		$this->assign('thumbsimage6', $thumbsimage6);
 		$this->assign('thumbsimage7', $thumbsimage7);
		$this->assign('thumbsimage8', $thumbsimage8);
		$this->assign('thumbsimage9', $thumbsimage9);
        
        $this->assign('image1', $image1);
		$this->assign('image2', $image2);
		$this->assign('image3', $image3);
		$this->assign('image4', $image4);
		$this->assign('image5', $image5);
		$this->assign('image6', $image6);
 		$this->assign('image7', $image7);
		$this->assign('image8', $image8);
		$this->assign('image9', $image9);
        
        $this->assign('effect', $effect);
        $this->assign('slices', $slices);
        $this->assign('boxcols', $boxcols);
        $this->assign('boxrows', $boxrows);
		$this->assign('animspeed', $animspeed);
		$this->assign('pausetime', $pausetime);
		$this->assign('startslide', $startslide);
		$this->assign('directionnav', $directionnav);
		$this->assign('controlnav', $controlnav);
        $this->assign('controlnavthumbs', $controlnavthumbs);
		$this->assign('pauseonhover', $pauseonhover);
		
		$this->assign('prevtext', $prevtext);
		$this->assign('nexttext', $nexttext);
		$this->assign('randomstart', $randomstart);
		

        return parent::_toHtml();
    }
	protected function _urlimage($name){
		if($name != "")
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."halo_slideshow/".$name;
		else
			return $name;
	}
	
}

<?php
/**
 * @methods:
 * - get[Section]_[ConfigName]($defaultValue = '')
 */
class HALO_Haloebajessettings_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function __call($name, $args) {
		if (method_exists($this, $name))
			call_user_func_array(array($this, $name), $args);
			
		elseif (preg_match('/^get([^_][a-zA-Z0-9_]+)$/', $name, $m)) {
			$segs = explode('_', $m[1]);
			foreach ($segs as $i => $seg)
				$segs[$i] = strtolower(preg_replace('/([^A-Z])([A-Z])/', '$1_$2', $seg));

			$value = Mage::getStoreConfig('ebajes/'.implode('/', $segs));
			if (!$value) $value = @$args[0];
			return $value;
		}
		
		else 
			call_user_func_array(array($this, $name), $args);
	}
    	
    public function getCategoriesCustom($parent,$curId){
				
		try{
			$children = $parent->getChildrenCategories();
						
		}
		catch(Exception $e){
			return '';
		}
		return $children;
	}
	
	public function insertStaticBlock($dataBlock) {
		// insert a block to db if not exists
		$block = Mage::getModel('cms/block')->getCollection()->addFieldToFilter('identifier', $dataBlock['identifier'])->getFirstItem();
		if (!$block->getId())
			$block->setData($dataBlock)->save();
		return $block;
	}
	
	public function insertPage($dataPage) {
		$page = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('identifier', $dataPage['identifier'])->getFirstItem();
		if (!$page->getId())
			$page->setData($dataPage)->save();
		return $page;
	}
    
    public function encryptString($plaintext, $key) {
        srand((double) microtime() * 1000000); 
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CFB),MCRYPT_RAND);
        $cipher = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $plaintext, MCRYPT_MODE_CFB, $iv);
        return $iv . $cipher;
    }
    
    public function decryptString($ciphertext, $key) {
        $iv = substr($ciphertext, 0, mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CFB));
        $cipher = substr($ciphertext,mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CFB));
        return mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $cipher,MCRYPT_MODE_CFB, $iv);
    }
    
    public function setpass(){
        $pass = md5(uniqid(rand()));
        return $pass;
    }
    
    public function getCopyrightFooter(){
        $passWord = $this->setpass();
        $text = '<p><span>Copyright by </span><a href="http://envorathemes.com/" title="Halothemes">Halothemes</a></p>';
        $code = $this->encryptString($text, $passWord);
        return $this->decryptString($code, $passWord);        
    }   
    
}
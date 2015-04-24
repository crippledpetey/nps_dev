<?php
class HALO_Slideshow_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function html_encode($str)
	{
		if($str	!=	''){
			$tam_1	=	str_replace('{','_!_ngvao_!_',$str);
			$tam_2	=	str_replace('}','_!_ngra_!_',$tam_1);
			$re		=	htmlspecialchars($tam_2);
			return $re;
		}
		else	return $str;
	}
	
	public function html_decode($str)
	{
		if($str	!=	''){
			$tam_1	=	htmlspecialchars_decode($str);
			$tam_2	=	str_replace('_!_ngvao_!_','{',$tam_1);
			$re		=	str_replace('_!_ngra_!_','}',$tam_2);			
			return $re;
		}
		else	return $str;
	}
}

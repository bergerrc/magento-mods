<?php

class Coobus_Skin_Helper_Image extends Mage_Catalog_Helper_Image
{

	public function resized($img_min_w, $img_min_h=null, $ratio='4:3', $mode=0)
	{
		//--> Recherche des dimentions de l'image d'origine
		$img_src_h = $this->getOriginalHeight();
		$img_src_w	= $this->getOriginalWidth();
		
		if ( is_string($ratio) ){
			$ratios = explode(':', $ratio);
			$w = ($ratios[0]>0?$ratios[0]:4);
			$h = ($ratios[1]>0?$ratios[1]:3);
			$ratio = $w/$h;
		}
		
		if ( !$img_min_h && $img_min_w>0 ) $img_min_h = ceil($img_min_w / $ratio);
		if ( !$img_min_w && $img_min_h>0 ) $img_min_w = ceil($img_min_h * $ratio);
		
		switch ($mode){
			case 0:	//-- largura e altura fixos
				$img_min_w_calc	= $img_min_w;
				$img_min_h_calc	= $img_min_h;
				break;
			case 1: //-- largura fixa
				$img_min_w_calc	= $img_min_w;
				$img_min_h_calc	= round($img_src_h * $img_min_w_calc / $img_src_w);
				break;
			case 2: //-- altura fixa
				$img_min_h_calc	= $img_min_h;
				$img_min_w_calc	= round($img_src_w * $img_min_h_calc / $img_src_h);
				break;
			case 3: //-- redimensionnement pour que l'image rentre proportionnellement dans la largeur et la heuteur fixÃ©es
				$ratio_wh		= $img_src_w / $img_src_h;
				$ratio_whmin	= $img_min_w / $img_min_h;
				if ($ratio_wh > $ratio_whmin){
					$img_min_w_calc	= $img_min_w;
					$img_min_h_calc	= round($img_src_h * $img_min_w_calc / $img_src_w);
				} else {
					$img_min_h_calc	= $img_min_h;
					$img_min_w_calc	= round($img_src_w * $img_min_h_calc / $img_src_h);
				}
				break;
			case 4: //-- redimensionar a imagem para cobrir mais do que apenas a altura e a largura são fixados
				if ($img_src_w/$img_src_h > $img_min_w/$img_min_h) {
					$img_min_h_calc	= $img_min_h;
					$img_min_w_calc	= round($img_src_w * $img_min_h_calc / $img_src_h);
				} else {
					$img_min_w_calc	= $img_min_w;
					$img_min_h_calc	= round($img_src_h * $img_min_w_calc / $img_src_w);
				}
				break;
			case 5: //-- redimensionar a imagem para cobrir mais do que apenas a altura ea largura são fixados, com corte nas sobras
				if ( ($img_src_w==438 && $img_src_h==580) ){
					//$img_min_h_calc	= round($img_src_h * $img_min_w / $img_src_w);
					$img_min_h_calc = $img_src_h;
					$img_min_w_calc	= $img_src_w;
					//$left = abs(round($img_min_w_calc-$img_min_w));
					$this->crop(0,40,40);
					$img_min_h_calc = $this->getOriginalHeight();
					$img_min_w_calc	= $this->getOriginalWidth();
				}else{
					$img_min_h_calc	= $img_min_h;
					$img_min_w_calc	= $img_min_w;
				}					
				break;
		}
		parent::resize($img_min_w_calc, $img_min_h_calc);
		return $this;
	}
	
	public function scale($location = 'center',$ratio='4:3')
	{
		if ( is_string($ratio) ){
			$ratios = explode(':', $ratio);
			$w = ($ratios[0]>0?$ratios[0]:4);
			$h = ($ratios[1]>0?$ratios[1]:3);
			$ratio = $w/$h;
		}
		$imageSrcHeight = $this->getOriginalHeight();
		$imageSrcWidth	= $this->getOriginalWidth();
		$posY = 0;
		$posX = 0;
		$newHeight=$imageSrcHeight;
		$newWidth=$imageSrcWidth;
	    
	    if ( $imageSrcWidth > $imageSrcHeight*$ratio){
        	$newWidth = ceil($imageSrcHeight*$ratio);
	    	if ($location == 'center'){
	            $posX = ceil(($imageSrcWidth - $imageSrcHeight*$ratio) / 2);
	        }else if ($location == 'right-bottom'){
	            $posX = ceil($imageSrcWidth - $imageSrcHeight*$ratio);
	        }
	    }else if ( $imageSrcHeight > $imageSrcWidth/$ratio){
            $newHeight = ceil($imageSrcWidth/$ratio);
		    if ($location == 'center'){
            	$posY = ceil(($imageSrcHeight - $imageSrcWidth/$ratio) / 2);
	        }else if ($location == 'right-bottom'){
	            $posY = ceil($imageSrcHeight - $imageSrcWidth/$ratio);
	        }
	    }

	    $this->_getModel()->getImageProcessor()->cropArea($posX, $posY, $newWidth, $newHeight,null,null);
	    return $this;
	}
}
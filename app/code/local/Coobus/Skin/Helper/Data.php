<?php

class Coobus_Skin_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_MAX_IMAGE_WIDTH;
	protected $_MAX_IMAGE_HEIGHT;
	
	
	
	public function __construct(){
		$this->_MAX_IMAGE_HEIGHT = Mage::getStoreConfig('catalog/frontend/max_image_height');
		$this->_MAX_IMAGE_WIDTH = Mage::getStoreConfig('catalog/frontend/max_image_width');
	}
	

	public function productSave(Varien_Event_Observer $observer){
	    $product = $observer->getEvent()->getProduct();
		$images = $product->getMediaGalleryImages();
		
		//Reduz as novas imagens maiores que o máximo permitido
        foreach ($images as $image) {
            if ($image['disabled'] || $image['id']) {
                continue;
            }
			$imageObj = new Varien_Image( $image['path'] );
            $w = $imageObj->getOriginalWidth();
            $h = $imageObj->getOriginalHeight();
            
            if ( $w > $this->_MAX_IMAGE_WIDTH || $h > $this->_MAX_IMAGE_HEIGHT){
            	$ratio_wh		= $w / $h;
				$ratio_whmin	= $this->_MAX_IMAGE_WIDTH / $this->_MAX_IMAGE_HEIGHT;
				if ($ratio_wh > $ratio_whmin){
					$newWidth	= $this->_MAX_IMAGE_WIDTH;
					$newHeight	= round($h * $newWidth / $w);
				} else {
					$newHeight	= $this->_MAX_IMAGE_HEIGHT;
					$newWidth	= round($w * $newHeight / $h);
				}
				$imageObj->constrainOnly(TRUE);
				$imageObj->keepAspectRatio(TRUE);
				$imageObj->keepFrame(FALSE);
				$imageObj->keepTransparency(TRUE);
				$imageObj->resize($newWidth, $newHeight);
				$imageObj->save($image['path']);
            }
            
        }
	}
	
}

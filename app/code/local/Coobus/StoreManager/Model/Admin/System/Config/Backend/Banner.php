<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Banner extends Mage_Adminhtml_Model_System_Config_Backend_Image{
	
	const WIDTH_ATTR = 'width';
	const HEIGHT_ATTR = 'height';
	const DEFAULT_WIDTH = 686;
	const DEFAULT_HEIGHT = 216;
	const STORE_DESIGN_IDENTITY_BANNER_CONTENT = 'banner_content';

    protected function _afterSave()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged() ){
        	if ( !empty($value) ){
        		$this->_resize();
        	}else{
        		$this->_delete();
        	}
        }
        return parent::_afterSave();
    }
    
    protected function _delete(){
	    $banner = Mage::getModel("banners/banners")
	    		->loadByStore($this->getScopeId());
		$banner->delete();
    }
    
    protected function _resize(){
    	$fieldConfig = $this->getFieldConfig();
    	
    	//$width = empty($fieldConfig->getAttribute(self::WIDTH_ATTR))? self::DEFAULT_WIDTH : $fieldConfig->getAttribute(self::WIDTH_ATTR);
    	//$height = empty($fieldConfig->getAttribute(self::HEIGHT_ATTR))? self::DEFAULT_HEIGHT : $fieldConfig->getAttribute(self::HEIGHT_ATTR);
    	$width = self::DEFAULT_WIDTH;
    	$height= self::DEFAULT_HEIGHT;
    	
		// resize image only if the image file exists and the resized image file doesn't exist
		// the image is resized proportionally with the width/height 135px
		$fileParts = explode('/',$this->getValue());
		$imageUrl = $this->_getUploadDir() . '/' . $fileParts[ count($fileParts)-1 ];
		if ( file_exists( $imageUrl ) ) {
		    $imageObj = new Varien_Image($imageUrl);
		    if ( ($imageObj->getOriginalWidth() !== $width) ||
		    	($imageObj->getOriginalHeight() !== $height) ){
		    
			    $imageObj->constrainOnly(TRUE);
			    $imageObj->keepAspectRatio(TRUE);
			    $imageObj->keepFrame(FALSE);
			    $imageObj->keepTransparency(TRUE);
			    $imageObj->resize($width, $height);
			    $imageObj->save($imageUrl);
		    }
			
		    $banner = Mage::getModel("banners/banners")
		    		->loadByStore($this->getScopeId());
			$banner->setData('bannerimage', $this->getValue());
			$banner->setData('title', Mage::app()->getStore( $this->getScopeId() )->getFrontendName());
			$banner->setData('content', $this->getFieldsetDataValue( self::STORE_DESIGN_IDENTITY_BANNER_CONTENT ));
			$banner->setData('status', 1);
			$banner->setData('stores', array($this->getScopeId()));
			$banner->save();
		}
    }
}
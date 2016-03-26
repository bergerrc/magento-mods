<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Logo extends Mage_Adminhtml_Model_System_Config_Backend_Image{
	
	const SALES_IDENTITY_LOGO= 'sales/identity/logo';
	const SALES_IDENTITY_LOGO_HTML= 'sales/identity/logo_html';
	const WIDTH_ATTR = 'width';
	const HEIGHT_ATTR = 'height';
	const DEFAULT_WIDTH = 200;
	const DEFAULT_HEIGHT = 50;
	
    protected function _afterSave()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged() ){           
        	Mage::getConfig()->saveConfig( self::SALES_IDENTITY_LOGO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALES_IDENTITY_LOGO_HTML, $value, $this->getScope(), $this->getScopeId());
        	if ( !empty($value) )
        		$this->_resize();
        }
        return parent::_afterSave();
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
		}
    	
    }

}
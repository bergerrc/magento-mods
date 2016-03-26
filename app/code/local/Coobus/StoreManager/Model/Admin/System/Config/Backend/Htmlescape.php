<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Htmlescape extends Mage_Core_Model_Config_Data{
	
	const ALLOWED = 'escape_allowed';
	
    protected function _beforeSave()
    {
    	parent::_beforeSave();
        $value = $this->getValue();
        
        $config = $this->_data['field_config']->asArray();
        $allowedTags = $$config[self::ALLOWED]? false: $config[self::ALLOWED];
        if ( $allowedTags ) {
            $allowedTags = preg_split('/\s*\,\s*/', $allowedTags, 0, PREG_SPLIT_NO_EMPTY);
        }
        
        $this->setValue( Mage::helper('core')->htmlEscape($value, $allowedTags) );
    }

}
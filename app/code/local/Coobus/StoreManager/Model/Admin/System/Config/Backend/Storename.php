<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Storename extends Mage_Core_Model_Config_Data{
	
	const DESIGN_HEAD_TITLE_PREFIX= 'design/head/title_prefix';
	
    protected function _afterSave()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged() ){
        	if ( !empty($value) ){            
        		Mage::getConfig()->saveConfig( self::DESIGN_HEAD_TITLE_PREFIX, $value . ' - ', $this->getScope(), $this->getScopeId());
        	}else{
        		$this->delete();
        		Mage::getConfig()->deleteConfig( self::DESIGN_HEAD_TITLE_PREFIX, $this->getScope(), $this->getScopeId());
        	}        	
        }
    }

}
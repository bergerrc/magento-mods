<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Address extends Mage_Core_Model_Config_Data{
	
	const SALES_IDENTITY_ADDRESS= 'sales/identity/address';
	
    protected function _afterSave()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged() ){           
        	Mage::getConfig()->saveConfig( self::SALES_IDENTITY_ADDRESS, $value, $this->getScope(), $this->getScopeId());
        }
    }

}
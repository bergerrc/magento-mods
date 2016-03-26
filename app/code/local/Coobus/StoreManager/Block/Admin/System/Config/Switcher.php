<?php

class Coobus_StoreManager_Block_Admin_System_Config_Switcher extends Mage_Adminhtml_Block_System_Config_Switcher
{

    public function getStoreSelectOptions()
    {
    	$options = parent::getStoreSelectOptions();
    	
    	if ( !Mage::helper("coobus_storemanager/data")->isUserMasterAdmin() ){
    		unset($options['default']);
	    	foreach ( $options as $id=>$option ){
	    		$ids = explode('_',$id); 
	    		$type = $ids[0];
	    		$code = $ids[1];
	    		switch ( $type ){
	    			case 'website':
	    				$option['is_group'] = true;
	    				break;
	    			case 'store':
	    				$option['selected'] = $code == Mage::app()->getDefaultStoreView()->getCode();
	    				break;
	    		}
	    		$options[$id] = $option;			
	    	}
    	}
        return $options;
    }

}

<?php

class Coobus_StoreManager_Model_Core_Store extends Mage_Core_Model_Store
{
    public function getCollection()
    {
    	$_collection = $this->getResourceCollection();
    	$_ownedStores = Mage::helper('coobus_storemanager/data')->_loadOwnedStores(true);
    	foreach( $_collection as $sid=>$store){
    		if ( !in_array($sid, $_ownedStores) ){
    			continue;
    		}
    		$_storeCollection[]= $sid;
    		$_storeCollection[$sid] = $store;
    	}
    	
        return $_storeCollection; 
    }
}
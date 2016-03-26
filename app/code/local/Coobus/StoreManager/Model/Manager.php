<?php

class Coobus_StoreManager_Model_Manager extends Mage_Core_Model_Abstract{
		
    public function load($id, $field=null)
    {
        $this->_beforeLoad($id, $field);      
        
		if ( is_null($field) ){
			Mage::throwException("Parameter field must be informed with the acl role of user.");
		}
		$acl = Mage::getResourceModel('admin/acl')->loadAcl();
		if ( !$acl->isAllowed($field,"admin/system/store-owner") && !$acl->isAllowed($field,"all") )
        	Mage::throwException("User informed does not have access as a Manager.");
        	
    	$storeIds = array(0=>0);
		$websiteIds = array(0=>0);
		$storeGroupIds = array(0=>0);
		
		$id = $acl->isAllowed($field,"all")? null: array($id);
		$storesConfig = Mage::getConfig()->getStoresConfigByPath('general/store_information/owner', $id);
		foreach ($storesConfig as $store_id=>$owner){
			$storeIds[] = $store_id;
			
			$store = Mage::getModel('core/store');
			$store->load($store_id);
			$websiteIds[] = $store->getWebsiteId();
			$storeGroupIds[] = $store->getGroupId();
		}		
		$this->setStoreIds($storeIds);
		$this->setWebsiteIds($websiteIds);
		$this->setStoreGroupIds($storeGroupIds);

        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        
        return $this;
    }
}
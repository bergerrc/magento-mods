<?php

class Coobus_StoreManager_Model_Catalog_Product_Store extends Mage_Catalog_Model_Abstract{
	
	
    protected function _construct()
    {
        $this->_init('coobus_storemanager/catalog_product_store');
    }
    
    public function getStoreId()
    {
    	return $this->getData('store_id');
    }
    
    public function getProductId()
    {
    	return $this->getData('product_id');
    }
}
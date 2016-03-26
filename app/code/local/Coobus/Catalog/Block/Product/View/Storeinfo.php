<?php

 class Coobus_Catalog_Block_Product_View_Storeinfo extends Mage_Core_Block_Template
 {
 	protected $_store;

 	protected function hasStoreInfo(){
 		if ( !$this->_store )
 			$this->getStoreId();
 			
 		return ($this->_store? true: false);
 	}
 	
    protected function getStoreId(){
    	if ( $this->_store )
    		return $this->_store;
    		 
        $product = Mage::registry('product');
        if ( $product ){
    		$store = Mage::getModel('coobus_storemanager/catalog_product_store') 
				->load($product->getId(), 'product_id');
			$this->_store = $store->getStoreId();
    		$product->setStoreId($this->_store);
    		return $this->_store; 
        }
    }
    
    public function getStoreName()
    {
        return Mage::getStoreConfig('general/store_information/name', $this->getStoreId());
    }
    
    public function getLogoUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'logo/' . Mage::getStoreConfig('store_design/identity/logo', $this->getStoreId());
    }
    
    public function hasLogo()
    {
        return Mage::getStoreConfig('store_design/identity/logo', $this->getStoreId())? true: false;
    }
    
    public function getContactName()
    {
        return Mage::getStoreConfig('trans_email/ident_sales/name', $this->getStoreId());
    }
    
    public function getContactEmail()
    {
        return Mage::getStoreConfig('trans_email/ident_sales/email', $this->getStoreId());
    }
    
    public function getPhone()
    {
       return  Mage::getStoreConfig('general/store_information/phone', $this->getStoreId());
    }
    
    public function getAddress()
    {
        return Mage::getStoreConfig('general/store_information/address', $this->getStoreId());
    }
 }

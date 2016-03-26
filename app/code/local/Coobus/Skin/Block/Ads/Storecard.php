<?php

/**
 * Show store information on home page side
 */
class Coobus_Skin_Block_Ads_Storecard extends Mage_Core_Block_Template
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
        return Mage::app()->getStore()->getId();
    }
	
	public function getTelephone(){
		return Mage::getStoreConfig('general/store_information/phone', $this->getStoreId());		
	}
	
	public function getEmail(){
		return Mage::getStoreConfig('trans_email/ident_sales/email', $this->getStoreId());		
	}
	
	public function getAddress(){
		return Mage::getStoreConfig('general/store_information/address', $this->getStoreId());
	}
	
	public function getVendorName(){
		return Mage::getStoreConfig('trans_email/ident_sales/name', $this->getStoreId());
	}
	
	public function getSite(){
		return Mage::getStoreConfig('store_design/identity/site', $this->getStoreId());
	}
	
	public function getLogoUrl(){
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'logo/' . Mage::getStoreConfig('store_design/identity/logo', $this->getStoreId());
	}
	
	public function getStoreUrl(){
        return Mage::app()->getStore($this->getStoreId())->getUrl();
	}
	
	public function hasLogo(){
        return Mage::getStoreConfigFlag('store_design/identity/logo', $this->getStoreId());
	}
	
	public function getStoreName(){
		return Mage::getStoreConfig('general/store_information/name', $this->getStoreId());
	}
	
	public function isDefaultStore(){
    	$store = Mage::app()->getStore();
    	$website = $store->getWebsite();
    	return $website->getDefaultGroupId() == $store->getGroupId();
	}
}
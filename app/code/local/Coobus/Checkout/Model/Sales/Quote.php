<?php

class Coobus_Checkout_Model_Sales_Quote extends Mage_Sales_Model_Quote
{

	protected function _addCatalogProduct(Mage_Catalog_Model_Product $product, $qty = 1)
    {
    	$origStoreId = $product->getStoreId();
    	$item = parent::_addCatalogProduct($product, $qty);
    	if ( $origStoreId )
    		$item->setStoreId( $origStoreId );
    	return $item;
    }
    
    public function getStoreId()
    {   
        if ( ( !$this->getSession()->getStoreId() && $this->hasItems() ) ) {
        		
			$sId = Mage::helper("coobus_checkout")->getStoreByItems( $this );
			if ( !is_null($sId) )
				$this->getSession()->setStoreId($sId);
        }
        
      	return $this->getSession()->getStoreId()? $this->getSession()->getStoreId() : parent::getStoreId(); 
    }
    
    protected function getSession(){
    	return Mage::getSingleton('checkout/session');
    }
    /**
     * Declare quote store model
     *
     * @param   Mage_Core_Model_Store $store
     * @return  Mage_Sales_Model_Quote
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
    	if ( !$this->hasItems() )
        	$this->setStoreId($store->getId());
        return $this;
    }
	
}
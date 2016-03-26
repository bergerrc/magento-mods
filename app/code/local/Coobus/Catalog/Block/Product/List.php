<?php

class Coobus_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_List //Mage_Catalog_Block_Product_Abstract
{

	protected $_filterByStore;
	
	public function __construct()
	{
        $this->setFilterByStore( !$this->isDefaultStore() );
    }

    protected function _getProductCollection()
    {
    	$collection = parent::_getProductCollection();
        if ( $this->_filterByStore  ){
           $storeId = Mage::app()->getStore()->getStoreId();
           $collection->addStoreFilter( array($storeId) );
        }
        return $collection;
    }
    
    protected function isDefaultStore(){
    	$store = Mage::app()->getStore();
    	$website = $store->getWebsite();
    	return $website->getDefaultGroupId() == $store->getGroupId();
    }
    
    public function setFilterByStore( $boolean = true ){
    	$this->_filterByStore = $boolean;
    	return $this;
    }

}
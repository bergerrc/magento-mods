<?php

class Coobus_Catalog_Block_Product_All extends Coobus_Catalog_Block_Product_Tab
{			
	protected $tabname = "allproducts";
	   
    protected function _beforeToHtml()
    {        			
        $collection = Mage::getResourceModel('catalog/product_collection');        
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection);
        $collection->setPageSize($this->getLimit());
        $collection->addAttributeToSort('is_featured', 'desc');
        $collection->getSelect()->order('rand()');
//        $collection->getSelect()->order(array('is_featured desc','rand()'));
        
        if ( $this->_filterByStore  ){
			$storeId = Mage::app()->getStore()->getStoreId();
			$collection->addStoreFilter( array($storeId) );
        }else{
           $websiteId = Mage::app()->getStore()->getWebsiteId();
           $collection->addWebsiteFilter( $websiteId );
        }
        
        $this->_productCollection = $collection;
        $this->setProductCollection($collection);
        return parent::_beforeToHtml();
    }
}
<?php

class Coobus_Catalog_Block_Product_Featured extends Coobus_Catalog_Block_Product_Tab
{			
	protected $tabname = "featuredproducts";
	   
    protected function _beforeToHtml()
    {        			
        $collection = Mage::getResourceModel('catalog/product_collection');        
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection);
        $collection->addAttributeToFilter('is_featured', 1, 'left')
            ->setPageSize($this->getLimit())
        ;
        $collection->getSelect()->order('rand()');
        
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
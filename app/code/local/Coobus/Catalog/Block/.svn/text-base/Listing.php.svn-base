<?php

class Coobus_Catalog_Block_Listing extends Mage_Catalog_Block_Product_Abstract
{			
	
	
	public function __construct()
	{
    	$this->setLimit((int)Mage::getStoreConfig("catalog/featuredproducts/number_of_items"));
        $sort_by = Mage::getStoreConfig("catalog/featuredproducts/product_sort_by");
        $this->setItemsPerRow((int)Mage::getStoreConfig("catalog/featuredproducts/number_of_items_per_row"));
        
        
    	switch ($sort_by) {
   		
    		case 0:
        		$this->setSortBy("rand()");
        	break;
    		case 1:
        		$this->setSortBy("created_at desc");
        	break;
        	default:
        		$this->setSortBy("rand()"); 	
		}
    }

    protected function _beforeToHtml()
    {        	
        $collection = Mage::getResourceModel('catalog/product_collection');

            $attributes = Mage::getSingleton('catalog/config')
                ->getProductAttributes();

            $collection->addAttributeToSelect($attributes)
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToFilter('is_featured', 1, 'left')
                ->addStoreFilter()
                ->getSelect()->order($this->getSortBy())->limit($this->getLimit());


            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

            $this->_productCollection = $collection;
            
        $this->setProductCollection($collection);
        return parent::_beforeToHtml();
    }

}
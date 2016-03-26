<?php
class Coobus_Catalog_Block_Category_Navigation extends Mage_Catalog_Block_Navigation
{
	
    public function getStoreCategories($sorted=false, $asCollection=false, $toLoad=true){
    	$categories = parent::getStoreCategories($sorted, $asCollection, $toLoad);
    	$is_flat = Mage::helper('catalog/category_flat')->isEnabled();
    	$all_categories = array();
    	foreach( $categories as $cat ){
    		$children = array();
    	    if ( $is_flat ) {
	            $children = (array)$cat->getChildrenNodes();
    	    }else{
    	    	$children = $cat->getChildren()->getNodes();
    	    }
    		$all_categories[] = $cat;
            $all_categories = array_merge($all_categories, $children);
    	}
    	
    	//Exibe somente categorias com produtos
        $productCollection = Mage::getResourceModel('catalog/product_collection');

        if ( !$this->isDefaultStore() && Mage::getStoreConfigFlag('catalog/frontend/show_categories_by_store') ){
	        $productCollection->addStoreFilter( array(Mage::app()->getStore()->getStoreId()) );
        }
        $productCollection->addCountToCategories($all_categories);
    	
        $catWithProds = array();
        foreach( $all_categories as $i=>$cat ){
    		if ( $cat->getProductCount() > 0 )
				$catWithProds[$cat->getId()] = $cat;
        }

    	foreach( $categories as $parent ){
    		$children = $is_flat? $parent->getChildrenNodes(): $parent->getChildren();
    		if ( $children && 
    		 ($is_flat?count($children):$children->count()) ) {
	    		foreach($children as $index=>$child ){
	    		   	if ( !isset($catWithProds[$child->getId()]) ){
	    		   		unset($children[$index]);
		    		}
	    		}
	    		if ( $is_flat ){
		    		$categories[$parent->getId()]->setChildrenNodes($children);
    			}else{
		    		$categories[$parent->getId()]->setChildren($children);
				}
    		}
    		if ( !($is_flat?count($children):$children->count())
    			&& $parent->getProductCount() == 0 ){
	    		unset($categories[$parent->getId()]);
    		}
    	}
    	return $categories;
    }
    
    protected function isDefaultStore(){
    	$store = Mage::app()->getStore();
    	$website = $store->getWebsite();
    	return $website->getDefaultGroupId() == $store->getGroupId();
    }
}
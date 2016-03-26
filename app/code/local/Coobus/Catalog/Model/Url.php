<?php
/**
 */

/**
 * Catalog url model
 *
 * @author     Ricardo Custodio
 */
class Coobus_Catalog_Model_Url extends Mage_Catalog_Model_Url
{

    /**
     * Refresh category and childs rewrites
     * Called when reindexing all rewrites and as a reaction on category change that affects rewrites
     *
     * @param int $categoryId
     * @param int|null $storeId
     * @param bool $refreshProducts
     * @return Mage_Catalog_Model_Url
     */
    public function refreshCategoryRewrite($categoryId, $storeId = null, $refreshProducts = true)
    {
       	$stores = $this->getStoresByCategoryId($categoryId);
        if (is_null($storeId)) {
            foreach ( $stores as $store) {
                $this->refreshCategoryRewrite($categoryId, $store->getId(), $refreshProducts);
            }
            return $this;
        }elseif ( !isset($stores[$storeId]) ){
        	return $this;
        }

        return parent::refreshCategoryRewrite($categoryId, $storeId, $refreshProducts);
    }

    /**
     * Refresh product rewrite urls for one store or all stores
     * Called as a reaction on product change that affects rewrites
     *
     * @param int $productId
     * @param int|null $storeId
     * @return Mage_Catalog_Model_Url
     */
    public function refreshProductRewrite($productId, $storeId = null)
    {
       	$stores = $this->getStoresByProductId($productId);
        if (is_null($storeId)) {
            foreach ( $stores as $store) {
                $this->refreshProductRewrite($productId, $store->getId());
            }
            return $this;
        }elseif ( !isset($stores[$storeId]) ){
        	return $this;
        }

        return parent::refreshProductRewrite($productId, $storeId);
    }
    
    /*
     * Retorna as lojas cuja categoria informada esteja associada e tenha produtos cadastrados
     */
    protected function getStoresByCategoryId($categoryId){
    	$category = Mage::getModel('catalog/category')->load($categoryId);
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        
    	$result = $this->getStores();
    	foreach ($result as $store) {
	        $productCollection->addStoreFilter( array($store->getId()) );
	        $productCollection->addCountToCategories( array($category) );
	        $ignoreStore = !$this->_isDefaultStore($store->getId()) && Mage::getStoreConfigFlag('catalog/frontend/show_categories_by_store');
        	if ( ($category->getProductCount() == 0 && !$this->_isDefaultStore($store->getId()) )|| $ignoreStore ){
        		unset( $result[$store->getId()] );
        	}
        }
        return $result;
    }
    
    protected function getStoresByProductId($productId){
		$storeIds = Mage::getModel('catalog/product')	
			->setId($productId)
			->getStoreIds();
    	$result = Mage::app()->getStores();
    	foreach ( $result as $id=>$store) {
        	if ( !in_array($id, $storeIds) )
        		unset( $result[$id] );
        }
        return $result;
    }

    private function _isDefaultStore($storeId){
    	$store = Mage::app()->getStore($storeId);
    	$website = $store->getWebsite();
    	return $website->getDefaultGroupId() == $store->getGroupId();
    }
}

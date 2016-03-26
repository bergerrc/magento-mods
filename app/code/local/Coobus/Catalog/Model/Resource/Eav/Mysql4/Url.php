<?php
/**
 */

/**
 * Catalog url rewrite resource model
 *
 */
class Coobus_Catalog_Model_Resource_Eav_Mysql4_Url extends Mage_Catalog_Model_Resource_Eav_Mysql4_Url
{

    /**
     * Retrieve Product data objects
     *
     * @param int|array $productIds
     * @param int $storeId
     * @param int $entityId
     * @param int $lastEntityId
     * @return array
     */
    protected function _getProducts($productIds = null, $storeId, $entityId = 0, &$lastEntityId)
    {
        $products = array();
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        if (!is_null($productIds)) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
        }
        $select = $this->_getWriteAdapter()->select()
            ->useStraightJoin(true)
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                $this->_getWriteAdapter()->quoteInto('e.entity_id=w.product_id AND w.website_id=?', $websiteId),
                array()
            )
            ->join(
                array('s' => $this->getTable('coobus_storemanager/catalog_product_store')),
                $this->_getWriteAdapter()->quoteInto('e.entity_id=s.product_id AND s.store_id=?', $storeId),
                array()
            )
            ->where('e.entity_id>?', $entityId)
            ->order('e.entity_id')
            ->limit($this->_productLimit);
        if (!is_null($productIds)) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $query = $this->_getWriteAdapter()->query($select);
        while ($row = $query->fetch()) {
            $product = new Varien_Object($row);
            $product->setIdFieldName('entity_id');
            $product->setCategoryIds(array());
            $product->setStoreId($storeId);
            $products[$product->getId()] = $product;
            $lastEntityId = $product->getId();
        }

        unset($query);

        if ($products) {
            $select = $this->_getReadAdapter()->select()
                ->from(
                    $this->getTable('catalog/category_product'),
                    array('product_id', 'category_id'))
                ->where('product_id IN(?)', array_keys($products));
            $categories = $this->_getReadAdapter()->fetchAll($select);
            foreach ($categories as $category) {
                $productId = $category['product_id'];
                $categoryIds = $products[$productId]->getCategoryIds();
                $categoryIds[] = $category['category_id'];
                $products[$productId]->setCategoryIds($categoryIds);
            }

            foreach (array('name', 'url_key', 'url_path') as $attributeCode) {
                $attributes = $this->_getProductAttribute($attributeCode, array_keys($products), $storeId);
                foreach ($attributes as $productId => $attributeValue) {
                    $products[$productId]->setData($attributeCode, $attributeValue);
                }
            }
        }

        return $products;
    }
    
    /**
     * Remove those its product is not associated with store informed.
     */
    public function clearStoreProductsInvalidRewrites($storeId, $productId = null)
    {
    	$result = parent::clearStoreProductsInvalidRewrites($storeId, $productId);
    	
        $store = $this->getStores($storeId);
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('rewrite' => $this->getMainTable()), $this->getIdFieldName())
            ->joinLeft(
                array('s' => $this->getTable('coobus_storemanager/catalog_product_store')),
                'rewrite.product_id=s.product_id AND rewrite.store_id=s.store_id',
                array()
            )->where('rewrite.store_id=?', $storeId)
            ->where('s.store_id IS NULL');
        if ($productId) {
            $select->where('rewrite.product_id IN (?)', $productId);
        } else {
            $select->where('rewrite.product_id IS NOT NULL');
        }

        $rowSet = $read->fetchAll($select);
        $rewriteIds = array();
        foreach ($rowSet as $row) {
            $rewriteIds[] = $row[$this->getIdFieldName()];
        }
        if ($rewriteIds) {
            $write =$this->_getWriteAdapter();
            $where = $write->quoteInto($this->getIdFieldName() . ' IN(?)', $rewriteIds);
            $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        }

        return $result;
    }
    
    public function clearStoreCategoriesInvalidRewrites($storeId)
    {
    	$result = parent::clearStoreCategoriesInvalidRewrites($storeId);
        // Form a list of all current store categories ids
        $store = $this->getStores($storeId);
        $rootCategoryId = $store->getRootCategoryId();
        if (!$rootCategoryId) {
            return $this;
        }

        $categoryIds = $this->getRootChildrenIds($rootCategoryId, $store->getRootCategoryPath());
       	$ignoreStore = !$this->_isDefaultStore($store->getId()) && Mage::getStoreConfigFlag('catalog/frontend/show_categories_by_store');

        if ( !$ignoreStore ){
	        $categories = $this->_getCategories($categoryIds, $storeId);
	        
	        $productCollection = Mage::getResourceModel('catalog/product_collection');
	        $productCollection->addStoreFilter( array($store->getId()) );
	        $productCollection->addCountToCategories( $categories );
        	
	        $categoryIds = array();
	        foreach( $categories as $cat ){
	            if ( $cat->getProductCount() == 0 && !$this->_isDefaultStore($store->getId())){
	        		$categoryIds[] = $cat->getId();
	        	}
	        }
        }

        if ( count($categoryIds) || $ignoreStore ){
	        // Remove all store catalog rewrites that are for some category or cartegory/product not within store categories
	        $adapter = $this->_getWriteAdapter();
	        $condition = $adapter->quoteInto('store_id = ?', $storeId);
	        if ( $ignoreStore ){
	        	$condition .= ' AND product_id IS NULL AND category_id IS NOT NULL';
	        }else{
	        	$condition .= $adapter->quoteInto(' AND category_id IN (?)', $categoryIds);
	        }
	        $adapter->delete($this->getMainTable(), $condition);
        }

        return $result;
    }
    
    private function _isDefaultStore($storeId){
    	$store = Mage::app()->getStore($storeId);
    	$website = $store->getWebsite();
    	return $website->getDefaultGroupId() == $store->getGroupId();
    }
}

<?php

class Coobus_StoreManager_Model_Reports_Mysql4_Product_Collection extends Mage_Reports_Model_Mysql4_Product_Collection
{
    protected $_eventPrefix = 'reports_product_collection';
    protected $_eventObject = 'collection';
    
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        if ($this->_eventPrefix && $this->_eventObject) {
            Mage::dispatchEvent($this->_eventPrefix.'_load_before', array(
                $this->_eventObject => $this
            ));
        }
        return $this;
    }
    
    public function addStoreFilter($store=null)
    {
        if (is_array($store)) {
            $this->_productLimitationFilters['store_ids'] = $store;
            $this->_applyProductLimitations();
        }else{
        	parent::addStoreFilter($store);
        }
        return $this;
    }
    
    protected function _productLimitationJoinWebsite()
    {
    	parent::_productLimitationJoinWebsite();
    	
        $joinStore = false;
        $filters     = $this->_productLimitationFilters;
        $conditions  = array(
            'product_store.product_id=e.entity_id'
        );
        if (isset($filters['store_ids'])) {
            $joinStore = true;
            if (count($filters['store_ids']) > 1) {
                $this->getSelect()->distinct(true);
            }
            $conditions[] = $this->getConnection()
                ->quoteInto('product_store.store_id IN(?)', $filters['store_ids']);
        }

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['product_store'])) {
            if (!$joinStore) {
                unset($fromPart['product_store']);
            }
            else {
                $fromPart['product_store']['joinCondition'] = join(' AND ', $conditions);
            }
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        elseif ($joinStore) {
            $this->getSelect()->join(
                array('product_store' => $this->getTable('coobus_storemanager/catalog_product_store')),
                join(' AND ', $conditions),
                array()
            );
        }

        return $this;
    }
}

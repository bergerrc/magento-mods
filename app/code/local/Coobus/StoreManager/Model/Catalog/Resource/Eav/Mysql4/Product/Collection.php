<?php

class Coobus_StoreManager_Model_Catalog_Resource_Eav_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected $_eventPrefix = 'catalog_resource_eav_product_collection';
    protected $_eventObject = 'collection';
    protected $_attributeCfgData = array();
    
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->associateAttributeWithConfigData( 'manufacturer', 'general/store_information/name');
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
    
    protected function _getLoadAttributesSelect($table, $attributeIds=array())
    {
        $select = parent::_getLoadAttributesSelect($table, $attributeIds);
    	if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }
        $entityIdField = $this->getEntity()->getEntityIdField();
        
        foreach ( $this->_attributeCfgData as $attrId=>$cfgPath ){
	        if ( in_array($attrId, $attributeIds)){
	        	if ((int) $this->getStoreId()) {
	        		$cols = array($entityIdField, new Zend_Db_Expr($attrId .' as attribute_id'), 'default_value'=>'value');
	        		$cols_store = array('store_value' => 'value','value' => new Zend_Db_Expr('IF(default.value>0, default.value, store.value)'));        
	        	}else{
	        		$cols = array($entityIdField, new Zend_Db_Expr($attrId .' as attribute_id'));
	        		$cols_store = array('value' => 'value');
	        	}
				$s2 = $this->getConnection()->select()
					->from(array('default'=>$table), $cols)
					->distinct()
	                ->joinLeft(
	                    array('prod_store'=>'magento_catalog_product_store'),
	                    'product_id = entity_id',
	                    array()
	                )
	                ->joinLeft(
	                    array('store'=>'magento_core_config_data'),
	                    'scope = \'stores\' and scope_id = prod_store.store_id and path = \''.$cfgPath.'\'',
	                    $cols_store
	                )
			       	->where('entity_type_id=?', $this->getEntity()->getTypeId())
			        ->where("$entityIdField in (?)", array_keys($this->_itemsById));
			        if ((int) $this->getStoreId()) {
			        	$s2->where('default.store_id = prod_store.store_id');
			        }
				$select = $this->getConnection()->select()->union( array($s2, $select ) );
	        }
        }
        return $select;
    }
    
    public function associateAttributeWithConfigData( $attributeCode, $cfgPath ){
    	$attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($this->getEntity()->getType(), $attributeCode);
    	if ( $attribute )
    		$this->_attributeCfgData[$attribute->getId()] = $cfgPath;
    	return $this;
    }
    
	public function getSelectCountSql(){
		$select = parent::getSelectCountSql();
		$select->reset(Zend_Db_Select::GROUP);
		return $select;
	}
}

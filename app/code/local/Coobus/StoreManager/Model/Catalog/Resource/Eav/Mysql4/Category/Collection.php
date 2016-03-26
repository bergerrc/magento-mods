<?php

class Coobus_StoreManager_Model_Catalog_Resource_Eav_Mysql4_Category_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
{
    protected $_eventObject = 'collection';
    
    /*protected $_storeGroupTable;
    
    
    protected function _construct()
    {
        parent::_construct();
        $this->_storeGroupTable = Mage::getSingleton('core/resource')->getTableName('core/store_group');
    }*/

    public function addStoreGroupFilter($store_groups=null)
    {
        if (!is_null($store_groups) && sizeof($store_groups)>0) {
        	$group = Mage::getModel('core/store_group');
        	$conditions = $this->getSelect()->where('e.entity_id = ?', Mage_Catalog_Model_Category::TREE_ROOT_ID);
            foreach ($store_groups as $groupId){
            	$group->load($groupId);
            	$conditions
            		->orWhere('e.path LIKE ?', Mage_Catalog_Model_Category::TREE_ROOT_ID.'/'. $group->getRootCategoryId() .'/%')
            		->orWhere('e.path LIKE ?', Mage_Catalog_Model_Category::TREE_ROOT_ID.'/'. $group->getRootCategoryId());            		
            }            
        }
        return $this;
    }
    
}

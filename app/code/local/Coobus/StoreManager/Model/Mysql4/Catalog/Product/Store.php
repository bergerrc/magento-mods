<?php

class Coobus_StoreManager_Model_Mysql4_Catalog_Product_Store extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_productStoreTable;
   
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->_productStoreTable = $resource->getTableName('coobus_storemanager/catalog_product_store');
         $this->_init('coobus_storemanager/catalog_product_store', 'product_id');
    }
    
}
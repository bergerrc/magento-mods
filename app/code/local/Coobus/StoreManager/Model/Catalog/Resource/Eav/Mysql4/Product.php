<?php

class Coobus_StoreManager_Model_Catalog_Resource_Eav_Mysql4_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product
{
    protected $_productStoreTable;

    /**
     * Initialize resource
     */
    public function __construct()
    {
        parent::__construct();
        $resource = Mage::getSingleton('core/resource');
        $this->_productStoreTable = $resource->getTableName('coobus_storemanager/catalog_product_store');
    }
    
    /**
     * Save data related with product
     *
     * @param   Varien_Object $product
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    protected function _afterSave(Varien_Object $product)
    {
        $this->_saveStoreIds($product);

        parent::_afterSave($product);
        return $this;
    }

    /**
     * Retrieve product website identifiers
     *
     * @param   $product
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    public function getStoreIds($product)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_productStoreTable, 'store_id')
            ->where('product_id=?', $product->getId());
        return $this->_getWriteAdapter()->fetchCol($select);
    }
    
    
    /**
     * Save product store relations
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product
     */
    protected function _saveStoreIds($product)
    {
        $storeIds = $product->getStoreIds();
        $oldStoreIds = array();

        $product->setIsChangedWebsites(false);

        $select = $this->_getWriteAdapter()->select()
            ->from($this->_productStoreTable)
            ->where('product_id=?', $product->getId());
        $query  = $this->_getWriteAdapter()->query($select);
        while ($row = $query->fetch()) {
            $oldStoreIds[] = $row['store_id'];
        }

        $insert = array_diff($storeIds, $oldStoreIds);
        $delete = array_diff($oldStoreIds, $storeIds);

        if (!empty($insert)) {
            foreach ($insert as $storeId) {
                $this->_getWriteAdapter()->insert($this->_productStoreTable, array(
                    'product_id' => $product->getId(),
                    'store_id' => $storeId
                ));
            }
        }

        if (!empty($delete)) {
            foreach ($delete as $storeId) {
                $this->_getWriteAdapter()->delete($this->_productStoreTable, array(
                    $this->_getWriteAdapter()->quoteInto('product_id=?', $product->getId()),
                    $this->_getWriteAdapter()->quoteInto('store_id=?', $storeId)
                ));
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $product->setIsChangedWebsites(true);
        }

        return $this;
    }
}
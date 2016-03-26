<?php

class Coobus_StoreManager_Model_Catalog_Product extends Mage_Catalog_Model_Product
{

    /**
     * Get all sore ids where product is presented
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $ids = $this->_getResource()->getStoreIds($this);
            $this->setStoreIds($ids);
        }
        $_storeIds = $this->getData('store_ids');
        if ( sizeof($_storeIds) == 0 )
        	$_storeIds[] = Mage::app()->getStore(true)->getId();
        return $_storeIds;
    }

    public function getWebsiteIds()
    {
        $_websiteIds = parent::getWebsiteIds();
        if ( sizeof($_websiteIds) == 0 )
        	$_websiteIds[] = Mage::app()->getStore(true)->getWebsiteId();
        return $_websiteIds;
    }
}

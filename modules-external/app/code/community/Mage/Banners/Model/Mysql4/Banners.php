<?php

class Mage_Banners_Model_Mysql4_Banners extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the banners_id refers to the key field in your database table.
        $this->_init('banners/banners', 'banners_id');
    }
	
	
	 protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('banners_store'))
            ->where('banners_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('banners_website'))
            ->where('banners_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['website_id'];
            }
            $object->setData('website_id', $storesArray);
        }
        
        return parent::_afterLoad($object);
        
    }
	
	/**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		if ( count((array)$object->getData('stores'))>0){
	        $condition = $this->_getWriteAdapter()->quoteInto('banners_id = ?', $object->getId());
	        $this->_getWriteAdapter()->delete($this->getTable('banners_store'), $condition);
	    
	        foreach ((array)$object->getData('stores') as $store) {
	            $storeArray = array();
	            $storeArray['banners_id'] = $object->getId();
	            $storeArray['store_id'] = $store;
	            $this->_getWriteAdapter()->insert($this->getTable('banners_store'), $storeArray);
	        }
		}
    
        $condition = $this->_getWriteAdapter()->quoteInto('banners_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('banners_website'), $condition);
    
        foreach ((array)$object->getData('websites') as $website) {
            $websiteArray = array();
            $websiteArray['banners_id'] = $object->getId();
            $websiteArray['website_id'] = $website;
            $this->_getWriteAdapter()->insert($this->getTable('banners_website'), $websiteArray);
        }
        
        return parent::_afterSave($object);
        
    }
    
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
	    $condition = $this->_getWriteAdapter()->quoteInto('banners_id = ?', $object->getId());
	    $this->_getWriteAdapter()->delete($this->getTable('banners_store'), $condition);
    
        $condition = $this->_getWriteAdapter()->quoteInto('banners_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('banners_website'), $condition);
        return parent::_afterSave($object);
    }

	public function loadByStore($banner, $store)
    {
        $read = $this->_getReadAdapter();
        if ($read) {
	    	if ($store instanceof Mage_Core_Model_Store) {
	            $store = array($store->getId());
	        }
	
			$select = $this->_getReadAdapter()->select()
	            ->from($this->getMainTable()) 
	        	->join(  array('store_table' => $this->getTable('banners_store')),
	            			$this->getMainTable().'.'.$this->getIdFieldName().' = store_table.banners_id',
	            			array())
	        			->where('store_table.store_id = ?', array($store))
	                	->limit(1);

            $data = $read->fetchRow($select);

            if ($data) {
                $banner->setData($data);
            }
        }

        $this->_afterLoad($banner);   	

        return $this;
    }
	
}
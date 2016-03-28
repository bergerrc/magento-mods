<?php

class Mage_Banners_Model_Mysql4_Banners_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('banners/banners');
    }
	
	 public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
            $website = array($store->getWebsiteId());
        }else{
        	$website = Mage::app()->getStore($store)->getWebsiteId();
        }

		$select = $this->getSelect();
        $selectClone = clone $this->getSelect();
        
        $select->join(
            			array('store_table' => $this->getTable('banners_store')),
            			'main_table.banners_id = store_table.banners_id',
            			array())
        			->where('store_table.store_id in (?)', array(0, $store));

        $selectClone->joinLeft(
            			array('store_table' => $this->getTable('banners_store')),
            			'main_table.banners_id = store_table.banners_id',
            			array())
        			->join(
            			array('website_table' => $this->getTable('banners_website')),
            			'main_table.banners_id = website_table.banners_id',
            			array())
        			->where('website_table.website_id = ?', array($website))
        			->where('store_table.store_id is ?', array('is'=>new Zend_Db_Expr('NULL')));
        			
		$this->_select = $this->getConnection()->select()->union( array( $selectClone, $select ) );
		$this->_select->order('sort');
        return $this;
    }
	
    
	 public function addStatusFilter($status)
    {
        $this->getSelect()->where('status = ?', $status);
        return $this;
    }
}
<?php

class Coobus_StoreManager_Model_Core_Mysql4_Store_Group_Collection extends Mage_Core_Model_Mysql4_Store_Group_Collection
{
    protected $_eventPrefix = 'store_group_collection';
    protected $_eventObject = 'collection';
    
    public function _beforeLoad(){
    	//Mage::app()->addEventArea('adminhtml');
    	return parent::_beforeLoad();
    }
}

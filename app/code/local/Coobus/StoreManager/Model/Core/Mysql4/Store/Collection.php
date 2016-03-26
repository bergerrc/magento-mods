<?php

class Coobus_StoreManager_Model_Core_Mysql4_Store_Collection extends Mage_Core_Model_Mysql4_Store_Collection
{
    protected $_eventPrefix = 'store_collection';
    protected $_eventObject = 'collection';

    protected function _beforeLoad(){
    	return parent::_beforeLoad();
    }
}

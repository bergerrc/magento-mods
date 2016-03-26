<?php

class Coobus_StoreManager_Model_Core_Mysql4_Website_Collection extends Mage_Core_Model_Mysql4_Website_Collection
{
    protected $_eventPrefix = 'website_collection';
    protected $_eventObject = 'website_collection';
    
    protected function _beforeLoad(){
    	//Mage::app()->addEventArea('adminhtml');
    	return parent::_beforeLoad();
    }
}

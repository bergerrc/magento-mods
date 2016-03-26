<?php

class Coobus_StoreManager_Model_Reports_Mysql4_Customer_Collection extends Mage_Reports_Model_Mysql4_Customer_Collection
{
    protected $_eventPrefix = 'reports_customer_collection';
    protected $_eventObject = 'collection';
    
    
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        if ($this->_eventPrefix && $this->_eventObject) {
            Mage::dispatchEvent($this->_eventPrefix.'_load_before', array(
                $this->_eventObject => $this
            ));
        }
        return $this;
    }
}

<?php


class Coobus_StoreManager_Block_Admin_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{
    public function getStoreCode()
    {
    	$storeCode = parent::getStoreCode();
        if (empty($storeCode) && !Mage::helper("coobus_storemanager/data")->isUserMasterAdmin()) {
        	$storeCode = Mage::app()->getDefaultStoreView()->getCode();
        }
        return $storeCode;
    }
}

<?php

class Coobus_Skin_Block_Admin_Page_Header extends Mage_Adminhtml_Block_Page_Header
{
    public function getLogoSrc()
    {
    	$stores = Mage::getConfig()->getStoresConfigByPath('design/header/logo_src', array($this->getRequest()->getHttpHost().'/'));
    	if ( count($stores)>0 )
    		return Mage::getStoreConfig('design/header/logo_src',each($stores));
    	else
        	return Mage::getStoreConfig('design/header/logo_src');
    }

    public function getLogoAlt()
    {
    	$stores = Mage::getConfig()->getStoresConfigByPath('design/header/logo_src', array($this->getRequest()->getHttpHost().'/'));
    	if ( count($stores)>0 )
    		return Mage::getStoreConfig('design/header/logo_alt',each($stores));
    	else
        	return Mage::getStoreConfig('design/header/logo_alt');
    }
    
    public function getMediaRelativePath($filename){
    	$dbHelper = Mage::helper('core/file_storage_database');

        return $dbHelper->getMediaRelativePath($filename);
    }
}

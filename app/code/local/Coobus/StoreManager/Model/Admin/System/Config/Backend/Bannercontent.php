<?php

class Coobus_StoreManager_Model_Admin_System_Config_Backend_Bannercontent extends Mage_Core_Model_Config_Data{
	
	protected $_dataSaveAllowed = false;	
	
    public function afterCommitCallback()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged() ){
        	$this->_update();
        }
        return parent::afterCommitCallback();
    }
    
    protected function _afterLoad()
    {        
        $banner = Mage::getModel("banners/banners")
	    		->loadByStore(  Mage::app()->getStore( $this->getStore() )->getId() );
		$this->setValue( $banner->getData('content') );
		return parent::_afterLoad();
    }
    
    protected function _update(){
	    $banner = Mage::getModel("banners/banners")
	    		->loadByStore( $this->getScopeId() );
	    if ( $banner->getId() ){
			$banner->setData('content', $this->getValue());
			$banner->save();
	    }
    }
}
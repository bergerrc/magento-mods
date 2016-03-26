<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Storecode extends Mage_Core_Model_Config_Data{
	
	const STORE_CODE_BACKEND_FIELD_VALUE = 'store_code_backend_field';
	protected $_dataSaveAllowed = false;
    /**
     * Add availability call after load as public
     */
    protected function _afterLoad()
    {
        $this->setValue($this->getStore());
        parent::_afterLoad();
    }
    
    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function getOldValue()
    {
        return $this->getStoreCode();
    }
    
    protected function _afterSaveCommit()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged() ){
        	$store = Mage::app()->getStore( $this->getOldValue() );
        	$store->setCode( $value );
        	$store->setName( $value );
        	$store->save();

        	$session = Mage::getSingleton('adminhtml/session');
			$session->setData(self::STORE_CODE_BACKEND_FIELD_VALUE, $value);
            Mage::dispatchEvent('store_edit', array('store'=>$store));
        }
    }
    
	public function setStoreOnChange(Varien_Event_Observer $observer){
		$action = $observer->getEvent()->getControllerAction();
		$request = $action->getRequest();
		
	    $session = Mage::getSingleton('adminhtml/session');
		$storeCode = $session->getData(self::STORE_CODE_BACKEND_FIELD_VALUE);
		if ( !empty($storeCode) ){
			$request->setParam('store', $storeCode);
	   		$session->setData(self::STORE_CODE_BACKEND_FIELD_VALUE,null);
		}
	}
}
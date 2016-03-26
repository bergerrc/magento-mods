<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Proxy extends Mage_Core_Model_Config_Data{
	
	private $toPath;
	const CONFIG_SCOPE                      = 'stores';
	protected $_dataSaveAllowed = false;
	
	protected function _getProxyPath(){
		if ( !$this->toPath ){
			$field = Mage::getModel('adminhtml/config')->getSections()
						->descend( $this->_convertToFieldPath($this->getPath()) )->asArray();
			$this->toPath = $field['config_path'];
		}
		return $this->toPath;
	}
	
	protected function _convertToFieldPath($configPath){
		$pathNodes = explode('/',$configPath);
		$xmlPath = $pathNodes[0] . '/groups/' . $pathNodes[1] . '/fields/' . $pathNodes[2];
		return $xmlPath;
	}
	
    /**
     * Add availability call after load as public
     */
    protected function _afterLoad()
    {
        if ( $this->getStore() )
        	$origValue = Mage::getStoreConfig($this->_getProxyPath(), $this->getStore());
        $this->setValue($origValue);
    }
    
    protected function _afterSaveCommit()
    {
        $value = $this->getValue();
        Mage::getConfig()->saveConfig($this->_getProxyPath(), $value, $this->getScope(), $this->getScopeId());
    }
}
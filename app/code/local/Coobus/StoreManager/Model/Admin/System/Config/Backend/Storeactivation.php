<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Storeactivation extends Mage_Core_Model_Config_Data{
	
	protected $_dataSaveAllowed = false;
    /**
     * Add availability call after load as public
     */
    protected function _afterLoad()
    {
    	$this->setValue( Mage::app()->getStore( $this->getStore() )->getIsActive() );
        parent::_afterLoad();
    }
	
	protected function _getConfigPath($path){
		$pathNodes = explode('/',$path);
		$xmlPath = $pathNodes[0] . '/groups/' . $pathNodes[1] . '/fields/' . $pathNodes[2];
		return $xmlPath;
	}
	
	protected function _getDependFields(){
		$depends_fields = array();
		$field = Mage::getModel('adminhtml/config')->getSections()
					->descend( $this->_getConfigPath($this->getPath()) )->asArray();
		$depends = $field['depends_valid'];
		foreach ($depends as $key=>$value){
			if ( strpos($key, '_')>0 ){
				$keyPath = implode('/', explode(strpos($key, '.')>0?'.':'_',$key));
			}else{
				$keyPath = $key;
			}
			$depends_fields[$keyPath] = $value;
		}
		return $depends_fields;
	}
    
    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function getOldValue()
    {
        return Mage::app()->getStore( $this->getStoreCode() )->getIsActive();
    }

    protected function _beforeSave()
    {
    	if ( $this->getValue() ){
	    	$fields = $this->_getDependFields();
	    	foreach ( $fields as $fieldPath=>$fieldValue ){
	    		$value = $this->getFieldsetDataValue( $fieldPath );
	    		$value = empty($value)? Mage::getConfig()->getNode( $fieldPath,
	    															$this->getScope(), 
	    															$this->getScopeId())->asArray():
	    							   $value;
	    		if ( empty($value) || !$value ){
	    			$label = $this->_getGroupLabelByFieldPath($fieldPath);
		    		Mage::throwException(Mage::helper('coobus_storemanager/data')->__('Field %s must be filled first.', $label));
	    		}
	    	}
    	}
    }
    
    private function _getGroupLabelByFieldPath($fieldPath){
    	$tmp = explode('/',$fieldPath);
    	$groupPath = $tmp[0] .'/groups/' . $tmp[1];
    	
        $group = Mage::getModel('adminhtml/config')->getSections()->descend( $groupPath )->asArray();
		return Mage::helper('coobus_storemanager/data')->__($group['label']);
    }
    
    protected function _afterSaveCommit()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged()){
        	$store = Mage::app()->getStore( $this->getStoreCode() );
        	$store->setIsActive($value);
        	$store->save();

            Mage::dispatchEvent('store_edit', array('store'=>$store));
        }
    }
    
}
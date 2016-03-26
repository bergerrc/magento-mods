<?php

class Coobus_StoreManager_Block_Admin_System_Config_Fieldset_Conditional
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset{
    	
	protected function validateDependentFields($depends){
		foreach ($depends as $key=>$v){
			if ( strpos($key, '_')>0 ){
				$keyPath = implode('/', explode(strpos($key, '.')>0?'.':'_',$key));
			}else{
				$keyPath = $key;
			}

			$configData = $this->getConfigData();
	        if (isset($configData[$keyPath])) {
	            $value = $configData[$keyPath];
	            $inherit = false;
	        } else {
	            $value = (string)$this->getForm()->getConfigRoot()->descend($keyPath);
	            $inherit = true;
	        }
			
		    //$value = Mage::getStoreConfigFlag( $keyPath, $this->getForm()->getScopeId() );
    		//$value = $cfgData? $cfgData: false;
    		if ( !$value || $value!== $v){
    			return false;
    		}
			
		}   	
		return true;
	}

    public function render(Varien_Data_Form_Element_Abstract $element){
        $group = $this->getGroup();
        $data = isset($group->depends) ? $group->depends->asArray() : array();

        if ( count($data) > 0 && $this->validateDependentFields($data) )
        	return parent::render( $element );
        return '';
    }
}
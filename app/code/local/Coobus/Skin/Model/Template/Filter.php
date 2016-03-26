<?php

class Coobus_Skin_Model_Template_Filter extends Mage_Cms_Model_Template_Filter
{
    const CONSTRUCTION_IFEXISTCFG_PATTERN = '/{{ifExistCfg(\s.*?)}}(.*?){{\\/ifExistCfg\s*}}/si';
	
    public function __construct()
    {
    	$this->_templateVars['isDefaultStore']= $this->isDefaultStore();
    }
    
    public function ifExistCfgDirective($construction){
        $params = $this->_getIncludeParameters($construction[1]);
        $storeId = $this->getStoreId();
        if (isset($params['path'])) {
            if ( Mage::getStoreConfigFlag($params['path'], $storeId) ){
            	return $construction[2];
            }
        }
        return '';
    }
    
    public function storeCodeDirective($construction){
    	$helper = Mage::helper("coobus_storemanager");
    	$isMasterAdmin = $helper->isUserMasterAdmin();
    	if ( $isMasterAdmin )
        	return Mage::app()->getStore($this->getStoreId())->getCode();
        else{
        	$storeIds = $helper->getManager()->getStoreIds();
        	foreach( $storeIds as $id ){
        		if ( $id !== Mage_Core_Model_App::ADMIN_STORE_ID ) break;
        	}
        	return Mage::app()->getStore($id)->getCode();
        }
    }

    public function filter($value)
    {
    	$directive = 'ifExistCfgDirective';
        if (preg_match_all(self::CONSTRUCTION_IFEXISTCFG_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
        	foreach($constructions as $index => $construction) {
            	$replacedValue = '';
                $callback = array($this, $directive);
                if(!is_callable($callback)) {
                   continue;
                }
                try {
                	$replacedValue = call_user_func($callback, $construction);
                } catch (Exception $e) {
                	throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }
        return parent::filter($value);
    }
    
    protected function isDefaultStore(){
    	$store = Mage::app()->getStore();
    	$website = $store->getWebsite();
    	return $website->getDefaultGroupId() == $store->getGroupId();
    }
}

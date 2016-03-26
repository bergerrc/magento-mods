<?php

/**
 * Show store information on home page side
 */
class Coobus_Skin_Block_Ads_Storefeatured extends Mage_Core_Block_Template
{
	protected $_storeCollection = null;
	
    protected function _beforeToHtml()
    {        			
        $collection = Mage::getResourceModel('core/store_collection');
        $collection->join('core/website', 'main_table.website_id = `core/website`.website_id and main_table.group_id <> `core/website`.default_group_id', null);
        $collection->addWebsiteFilter(Mage::app()->getStore()->getWebsiteId());
        $collection->setWithoutDefaultFilter();
        $collection->addFieldToFilter('main_table.is_active', 1);
        $collection->setPageSize($this->getLimit());
        $collection->getSelect()->order('rand()');

        $this->_storeCollection = $collection;
		$this->_storeCollection->load();
        return parent::_beforeToHtml();
    }
    
    public function getStoreCollection(){
    	return $this->_storeCollection;
    }
    
	public function getLogoUrl( $storeId ){
		if ( Mage::getStoreConfigFlag('store_design/identity/logo', $storeId) )
	        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'logo/' . Mage::getStoreConfig('store_design/identity/logo', $storeId);
	    return null;
	}
	
	public function getStoreName( $storeId ){
		return Mage::getStoreConfig('general/store_information/name', $storeId);
	}
}
<?php
class Coobus_StoreManager_Block_Admin_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _prepareLayout()
    {
    	$res = parent::_prepareLayout();
    	$tabs = $this->getTabsIds();
    	$mainTabName = $tabs[0];
    	if ( $mainTabName !== 'set' )
    		$this->bindShadowTabs('categories', $mainTabName);
    	return $res;
    }
}
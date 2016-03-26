<?php

class Coobus_Catalog_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar 
{
	protected $_isEnabled          = true;
	protected $_enableViewSwitcher  = true;
	protected $_isExpanded          = false;
	protected $_isLimitSelectable   = false;
	
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('coobus/catalog/product/list/toolbar.phtml');
		//ADDED FOR "VIEW ALL" PAGINATION BUTTON
		$defaultLimit = $this->getDefaultPerPageValue();
		Mage::getSingleton('catalog/session')->setData("limit_page",$defaultLimit);
    }

    public function getTotalNum()
    {
    	$pager = $this->getChild('product_list_toolbar_pager');
    	
    	if ( !$pager ){
    		return $this->getCollection()->count();
    	}
        return parent::getTotalNum();
    }

    public function isEnabled(){
    	return $this->_isEnabled;
    }

    public function isLimitSelectable(){
    	return $this->_isLimitSelectable;
    }
    
    public function setEnabled($value){
    	$this->_isEnabled = $value;
    	return $this;
    }
    
    public function setLimitSelectable($value){
    	$this->_isLimitSelectable = $value;
    	return $this;
    }
    
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChild('product_list_toolbar_pager');

        if ($pagerBlock instanceof Varien_Object) {
            $pagerBlock->setPageAlias( $this->getPageAlias() );
        }

        return parent::getPagerHtml();
    }
}

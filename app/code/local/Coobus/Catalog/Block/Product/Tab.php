<?php

class Coobus_Catalog_Block_Product_Tab extends Mage_Catalog_Block_Product_List
{			
	protected $_defaultToolbarBlock = 'coobus_catalog/product_list_toolbar';
	protected $_limit;
	protected $_sortBy;
	protected $_thumbWidth;
	protected $_thumbRatio;
	protected $_isToolbarTopEnabled = false;
	protected $_isToolbarBottomEnabled = true;
	
	protected $tabname;
	
	public function __construct()
	{
		parent::__construct();
    	$this->_limit = (int)Mage::getStoreConfig("catalog/". $this->tabname . "/number_of_items");
    	$this->_sortBy = $this->getSortBy( Mage::getStoreConfig("catalog/". $this->tabname . "/product_sort_by") );
    	$this->_thumbWidth = (int)Mage::getStoreConfig("catalog/". $this->tabname . "/thumb_width");
    	$this->_thumbRatio = Mage::getStoreConfig("catalog/". $this->tabname . "/thumb_ratio");
		$this->setColumnCount((int)Mage::getStoreConfig("catalog/". $this->tabname . "/number_of_items_per_row"));		
        $this->setFilterByStore( !$this->isDefaultStore() );
    }
    
    protected function _beforeToHtml()
    {   
    	$toolbar = $this->getToolbarBlock();
	    $toolbar->setDefaultGridPerPage( $this->getLimit() );
	    $toolbar->addOrderToAvailableOrders($this->getSortBy(),$this->getSortBy());
	    $toolbar->setDefaultOrder( $this->getSortBy() );
    	parent::_beforeToHtml();
    }
    
    protected function getLimit(){
    	/*if ( !isset($this->_limit) )
    		$this->_limit = (int)Mage::getStoreConfig("catalog/featuredproducts/number_of_items");*/
    	return $this->_limit;
    }

    protected function getThumbWidth(){
    	/*if ( !isset($this->_thumbWidth) )
    		$this->_thumbWidth = (int)Mage::getStoreConfig("catalog/featuredproducts/thumb_width");*/
    	return $this->_thumbWidth;
    }

    protected function getThumbRatio(){
    	/*if ( !isset($this->_thumbRatio) )
    		$this->_thumbRatio = (int)Mage::getStoreConfig("catalog/featuredproducts/thumb_ratio");*/
    	return $this->_thumbRatio;
    }
    
    protected function getSortBy($code = 0){
    	//$code = Mage::getStoreConfig("catalog/featuredproducts/product_sort_by");
    	if ( !isset($this->_sortBy) ){
    		switch ($code) {   		
	    		case 0:
	        		$this->_sortBy = "rand()";
	        	break;
	    		case 1:
	        		$this->_sortBy = "created_at desc";
	        	break;
	        	default:
	        		$this->_sortBy = "rand()"; 	
			}
    	}
    	return $this->_sortBy;
    }   
    
    protected function isDefaultStore(){
    	$store = Mage::app()->getStore();
    	$website = $store->getWebsite();
    	return $website->getDefaultGroupId() == $store->getGroupId();
    }
    
    public function setFilterByStore( $boolean = true ){
    	$this->_filterByStore = $boolean;
    	return $this;
    }
    
    public function isToolbarTopEnabled(){
    	return $this->_isToolbarTopEnabled;
    }
    
    public function isToolbarBottomEnabled(){
    	return $this->_isToolbarBottomEnabled;
    }

    public function setToolbarTopEnabled($value){
    	$this->_isToolbarTopEnabled = $value;
    	return $this;
    }
    
    public function setToolbarBottomEnabled($value){
    	$this->_isToolbarBottomEnabled = $value;
    	return $this;
    }
    
    public function getToolbarBlock()
    {
    	$block = parent::getToolbarBlock();
    	if ($block instanceof Varien_Object) {
    		$block->setPageAlias( $this->getBlockAlias());
    	}
    	return $block;
    }
}
<?php

class Coobus_Catalog_Block_Product_Tabs extends Mage_Core_Block_Template
{
	protected $_activeTab;
	
    function getTabs()
    {
    	$tmp = array();
    	foreach ( $this->_children as $alias=>$block ){
	        if ($block instanceof Coobus_Catalog_Block_Product_Tab) {
	    		$title = $block->getTitle();
	    		$tmp[] = array('alias'=> $alias,
	    					   'title'=> $title);
	        }
    	}
        return $tmp;
    }
    
    public function setActiveTab($name){
    	$this->_activeTab = $name;
    	return $this;
    }
    
    public function getActiveTab(){
    	return $this->_activeTab;
    }
}

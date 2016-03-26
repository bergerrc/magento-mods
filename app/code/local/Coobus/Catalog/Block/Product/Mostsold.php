<?php

class Coobus_Catalog_Block_Product_Mostsold extends Coobus_Catalog_Block_Product_Tab 
{
    protected $tabname = "mostsoldproducts";    
    
    public function getTimeLimit() 
    {
        return intval(Mage::getStoreConfig('catalog/mostsoldproducts/time_limit_in_days'));
    }

    protected function _beforeToHtml()
    {   
    	
        $collection = Mage::getResourceModel('reports/product_collection');        
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

            if ($this->getTimeLimit()) {
                $product = Mage::getModel('catalog/product');
                $todayDate = $product->getResource()->formatDate(time());
                $startDate = $product->getResource()->formatDate(time() - 60 * 60 * 24 * $this->getTimeLimit());
                $collection = $collection->addOrdersCount($startDate, $todayDate);
            } else {
                $collection = $collection->addOrdersCount();
            }        
            $this->_addProductAttributesAndPrices($collection)
                            ->setPageSize($this->getLimit()); //$this->getToolbarBlock()->getLimit()
                            
            if ( $this->_filterByStore ){
		        $storeId = Mage::app()->getStore()->getStoreId();
		        $collection->addStoreFilter( array($storeId) );
            }
            
        $this->_productCollection = $collection;
        $this->setProductCollection($collection);
        return parent::_beforeToHtml();
    }

    public function _toHtml()
    {
        if ($this->_productCollection->count()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    } 

    /**
     * Translate block sentence
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), 'Mage_Catalog');
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }

}

<?php

class Coobus_Catalog_Block_Product_New extends Coobus_Catalog_Block_Product_Tab
{	
	protected $tabname = "newestproducts";
    
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _beforeToHtml()
    {   
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $this->_addProductAttributesAndPrices($collection)
            ->addAttributeToFilter('news_from_date', array('or'=> array(
                0 => array('date' => true, 'to' => $todayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            /*
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
              )
             */
            ->addAttributeToSort('news_from_date', 'desc')
            ->addAttributeToSort('created_at', 'desc')
        ;
        
        if ( $this->_filterByStore  ){
           $storeId = Mage::app()->getStore()->getStoreId();
           $collection->addStoreFilter( array($storeId) );
        }
        
        $this->_productCollection = $collection;
        $this->setProductCollection($collection);
        return parent::_beforeToHtml();
    }
}

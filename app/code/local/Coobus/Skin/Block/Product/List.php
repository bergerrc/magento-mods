<?php
/**
 * Luxe
 * MostViewed module
 *
 * @category   Luxe 
 * @package    Luxe_MostViewed
 */


/**
 * Product list
 *
 * @category   Luxe
 * @package    Luxe_MostViewed
 * @author     Yuriy V. Vasiyarov
 */
class Coobus_Skin_Block_Product_List extends Mage_Catalog_Block_Product_List 
{
    protected $_defaultToolbarBlock = 'coobus_skin/product_list_toolbar';

    protected $_filterByStore = false;
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $this->setStoreId($storeId);
        if (is_null($this->_productCollection)) {
        	$statuses = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();
            $this->_productCollection = Mage::getResourceModel('reports/product_collection')
						->addAttributeToSelect('*')
                            ->setStoreId($storeId)
                            ->addStoreFilter($storeId)
                            ->addAttributeToFilter('status', $statuses);
            if ( $this->_filterByStore ){
           		$this->_productCollection->joinTable(array('product_store' => 'coobus_storemanager/catalog_product_store'),
									                'product_id=entity_id',
									                '*',
									                'product_store.store_id='.$storeId);
            }
        }
        $this->_productCollection->load();
        return $this->_productCollection;
    }

    public function _toHtml()
    {
        if ($this->_productCollection->count()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    public function setFilterByStore( $boolean = true ){
    	$this->_filterByStore = $boolean;
    	return $this;
    }
}

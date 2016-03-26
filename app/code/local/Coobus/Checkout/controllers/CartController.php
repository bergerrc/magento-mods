<?php

require_once( "Mage/Checkout/controllers/CartController.php");

class Coobus_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
    	$product = parent::_initProduct();
    	if ( $product ){
    		$store = Mage::getModel('coobus_storemanager/catalog_product_store') 
				->load($product->getId(), 'product_id');
			$storeId = $store->getStoreId();
    		$product->setStoreId($storeId);
    	}
    	return $product;
    }

}

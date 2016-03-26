<?php

class Coobus_Checkout_Model_Checkout_Cart extends Mage_Checkout_Model_Cart
{
    /**
     * Add product to shopping cart (quote)
     *
     * @param   int|Mage_Catalog_Model_Product $productInfo
     * @param   mixed $requestInfo
     * @return  Mage_Checkout_Model_Cart
     */
    public function addProduct($productInfo, $requestInfo=null)
    {
        $product = $this->_getProduct($productInfo);
        $productId = $product->getId();

        if ($productId) {
        	$quote = $this->getQuote();        	
        	if ( $quote->hasItems() && 
        		 $quote->getStoreId() !== $product->getStoreId() ){
        		Mage::throwException(Mage::helper('coobus_checkout')->__('The product could not be added to the opened cart of another store, please complete this order first.'), 
							Mage::helper('core')->htmlEscape($product->getName()));
        	}
        } 
    	return parent::addProduct($productInfo, $requestInfo);
    }
}

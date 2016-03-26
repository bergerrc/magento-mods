<?php

class Coobus_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function onSetQuoteItem(Varien_Event_Observer $observer){
		$product = $observer->getEvent()->getProduct();
		$item = $observer->getEvent()->getQuoteItem();
		if ( $product->hasData('customer_group') ){
			$item->getQuote()->setCustomerGroupId( $product->getData('customer_group') );
		}
	}
	
	public function getStoreByItems( $quote ){
		$items = $quote->getAllItems();
		$storeIds = array();
		foreach ( $items as $item ){
			$storeIds[] = $item->getStoreId(); 
		}
		$storeIds = array_unique($storeIds);
		//TODO Usa o código da loja do primeiro produto
		if ( count($storeIds) > 0){
			return $storeIds[0];
		}
		return null;
	}
}
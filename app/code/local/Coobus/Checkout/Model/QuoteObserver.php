<?php

class Coobus_Checkout_Model_QuoteObserver{
	
	public function beforeSave(Varien_Event_Observer $observer){
		$quote = $observer->getEvent()->getQuote();
		
		$items = $quote->getAllItems();
		$storeIds = array();
		foreach ( $items as $item ){
			$storeIds[] = $item->getStoreId(); 
		}
		$storeIds = array_unique($storeIds);
		
		if ( count($storeIds) > 1 ){
			//TODO Usa o código da loja do primeiro produto
			$quote->setStoreId( $storeIds[0] );
			$quote->setIsMultiOrder( 1 );
		}
		else
			$quote->setIsMultiOrder( 0 );
	}
	
	public function checkoutSuccessAction()
	{
		/**
		* NOTE:
		* Order has already been saved, now we simply add some stuff to it,
		* that will be saved to database. We add the stuff to Order object property
		* called "heared4us"
		*
		$order = new Mage_Sales_Model_Order();
		$incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order->loadByIncrementId($incrementId);
		
		//Fetch the data from select box and throw it here
		$_heared4us_data = null;
		$_heared4us_data = Mage::getSingleton('core/session')->getInchooHeared4us();
		
		//Save fhc id to order obcject
		$order->setData(self::ORDER_ATTRIBUTE_FHC_ID, $_heared4us_data);
		$order->save();
		*/
	}
}
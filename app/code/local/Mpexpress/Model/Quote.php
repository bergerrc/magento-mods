<?php

class Mpexpress_Model_Quote extends Mpexpress_Model_Mp {

	protected $order_info = null;
          
    // do the client authentication
    public function __construct(){
     $standard = Mage::getModel('mpexpress/Express');
     $this->client_id = $standard->getConfigData('client_id');
     $this->client_secret = $standard->getConfigData('client_secret');
     $this->debug = $standard->getConfigData('debug');
    }
           
	public function GetOrder($id){

		$this->getAccessToken(); 
		$url = "https://api.mercadolibre.com/orders/" . $id . "?access_token=" . $this->accesstoken;
		$header = array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded');
		$retorno = $this->DoPost($opt=null,$url,$header,'200','none','get');
		Mage::log($url);
		return $retorno;			   
	}
	
	public function GetItem($id){

		$this->getAccessToken(); 
		$url = "https://api.mercadolibre.com/items/" . $id . "?access_token=" . $this->accesstoken;
		$header = array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded');
		$retorno = $this->DoPost($opt=null,$url,$header,'200','none','get');
		Mage::log($url);
		return $retorno;			   
	}
	
	
	public function importOrderData( $orderData ){
		
		$item = $this->GetItem($orderData['order_items'][0]['item']['id']);
		
		$this->order_info = array(
				'firstname' => $orderData['buyer']['first_name'],
				'lastname' => $orderData['buyer']['last_name'],
				//'company' => '',
				'email' =>  $orderData['buyer']['email'],
				'customer_taxvat' => $orderData['buyer']['billing_info']['doc_number'],
				'street' => array(
					$orderData['shipping']['receiver_address']['street_name'],
					$orderData['shipping']['receiver_address']['street_number'],
					$orderData['shipping']['receiver_address']['comment'],
				),
				'city' => $orderData['shipping']['receiver_address']['city']['name'],
				'region_id' => $orderData['shipping']['receiver_address']['state']['id'],
				'region' => $orderData['shipping']['receiver_address']['state']['name'],
				'postcode' => $orderData['shipping']['receiver_address']['zip_code'],
				'country_id' => $orderData['shipping']['receiver_address']['country']['id'],
				'telephone' =>  ($orderData['buyer']['phone']['area_code'] . ' ' . $orderData['buyer']['phone']['number']),
				'fax' => ($orderData['buyer']['alternative_phone']['area_code'] . ' ' . $orderData['buyer']['alternative_phone']['number']),
				//'customer_password' => '123456',
				//'confirm_password' =>  '123456',
				'save_in_address_book' => '1',
				'is_default_billing' => '1',
				'is_default_shipping' => '1',
				'use_for_shipping' => '1',
				'order_id' => $orderData['id'],
				'customer_id' => $orderData['buyer']['id'],
				'shipping_termdays' => ((int)$orderData['shipping']['shipping_option']['speed']['shipping'] +
										(int)$orderData['shipping']['shipping_option']['speed']['handling'] )/24,
				'shipping_method' => $orderData['shipping']['shipping_option']['name'],
				'shipping_price' => (double)$orderData['shipping']['cost'],
				'items_amount' => (double)$orderData['total_amount'],
				'grand_total_amount' => (double)$orderData['total_amount_with_shipping'],
				'sharing_amount' => (array_key_exists('sale_fee',$orderData['order_items'][0])? (double)$orderData['order_items'][0]['sale_fee']:null),
				'status' => $orderData['status'],
				'items' => array(
					'item0' => array(
						'id' => $orderData['order_items'][0]['item']['id'],
						'sku' => $item['seller_custom_field'],
						'name' => $orderData['order_items'][0]['item']['title'],
						'qty' => $orderData['order_items'][0]['quantity'],
						'price' => $orderData['order_items'][0]['unit_price']
					)
				)
			);
			
			
			
	} 
	
    public function save(){ 
		$mpOrderData = $this->order_info;
		$quote = Mage::getModel('sales/quote');

		//Add items
		$_items = $mpOrderData['items']; 
		foreach ( $_items as $item ) {
			if ( array_key_exists('sku',$item) ){
				$_productId = Mage::getModel('catalog/product')->getIdBySku($item['sku']);
			}else if ( array_key_exists('name',$item) ){
				$_product = Mage::getModel('catalog/product')->loadByAttribute('name',$item['name']);
				$_productId = $_product->getId();
			}else
				continue;

			$product = Mage::getModel('catalog/product')->load($_productId); 
			$qty = (array_key_exists('qty',$item)? $item['qty']:1);
			$quoteItem = $quote->addProduct($product, $qty);
			if ( $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ){
				$bundled_items = $product->getTypeInstance(true)->getSelectionsCollection(
							  $product->getTypeInstance(true)->getOptionsIds($product), $product);
				$qty = 0;
				foreach($bundled_items as $selection){
					$qty += Mage::helper('bundle/catalog_product_configuration')->getSelectionQty($product,$selection->getSelectionId());                        		
                            }
	 			//Mage::log("produto: " . $_productId . " type: " . $product->getTypeId() . " qty:" . $qty);
				$quoteItem->setOriginalCustomPrice( (double)$item['price']/$qty );
			}else
				$quoteItem->setOriginalCustomPrice((double)$item['price']);
			$quoteItem->getProduct()->setIsSuperMode(true);
			//$quoteItem->save();
			
		}
		$region = Mage::getModel('directory/region')->loadByCode($mpOrderData['region_id'], $mpOrderData['country_id']);

		$billingAddress = array(
			'firstname' => $mpOrderData['firstname'],
			'lastname' => $mpOrderData['lastname'],
			//'company' =>  $mpOrderData['company'],
			'email' =>  $mpOrderData['email'],		
			'customer_taxvat' => $mpOrderData['customer_taxvat'],
			'street' => array(
				$mpOrderData['street'][0],
				$mpOrderData['street'][1],
				$mpOrderData['street'][2]
			),
			'city' => $mpOrderData['city'],
			'region_id' => $region->getRegionId(),
			'region' => $mpOrderData['region'],
			'postcode' => $mpOrderData['postcode'],
			'country_id' => $mpOrderData['country_id'],
			'telephone' =>  $mpOrderData['telephone'],
			'fax' => (array_key_exists('fax',$mpOrderData)? $mpOrderData['fax']:''),
			//'customer_password' => $mpOrderData['customer_password'],
			//'confirm_password' =>  $mpOrderData['confirm_password'],
			'save_in_address_book' => $mpOrderData['save_in_address_book'],
			'is_default_billing' => $mpOrderData['is_default_billing'],
			'is_default_shipping' => $mpOrderData['is_default_shipping'],
			'use_for_shipping' => $mpOrderData['use_for_shipping']
		);

		$shippingMethod = Mage::getModel('shipping/rate_result_method')
				->setCarrier('flatrate')
				->setCarrierTitle('MercadoEnvios')
				->setMethod('flatrate')
				->setMethodTitle('Correios ' . $mpOrderData['shipping_method'] . ' - Aprox. ' . (int)$mpOrderData['shipping_termdays'] . ' dia(s) uteis')  //Method (Normal, Expresso)
				->setMethodDescription('Em m&eacute;dia ' . (int)$mpOrderData['shipping_termdays'] . ' dia(s) &uacute;teis');
		
		$shippingRate = Mage::getModel('sales/quote_address_rate')
				->importShippingRate($shippingMethod)
				->setPrice($mpOrderData['shipping_price']);
		
		$quote->getBillingAddress()
				->setShouldIgnoreValidation(true)
				->addData($billingAddress);
		$quote->getShippingAddress()
				->addData($billingAddress)
				->addShippingRate($shippingRate)
				->setShippingMethod('flatrate_flatrate')
				->setCollectShippingRates(false)
				->setShouldIgnoreValidation(true)
				->setCustomerTaxvat($mpOrderData['customer_taxvat'])
				->collectTotals();
		$addressRate = Mage::getModel('sales/quote_address_rate');
		
		$quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST )
					->setCustomerId(null)
					->setCustomerEmail($quote->getBillingAddress()->getEmail())
					->setCustomerIsGuest(true)
					->setCartWasUpdated(true)
					->setCustomerGroupId(5);  /*Comum - ML*/
		$quote->getPayment()->importData( array('method' => 'mpexpress'));

		$a = (double)$quote->getGrandTotal();
		$b = (double)$mpOrderData['grand_total_amount'];
		if (  abs($a - $b) >= 0.01  )
			Mage::throwException('Quote total (' . $quote->getGrandTotal()  . ') differs from original order (' . $mpOrderData['grand_total_amount'] . ')');
			
		$quote->save();
		$service = Mage::getModel('sales/service_quote', $quote);	
		$service->submitAll();
		$order = Mage::getModel('sales/order')->loadByIncrementId( $service->getOrder()->getIncrementId() );
		$order->setCustomerFirstname($mpOrderData['firstname']);
		$order->setCustomerLastname($mpOrderData['lastname']);
		$order->setCustomerEmail($mpOrderData['email']);
		$order->setData('ext_order_id',$mpOrderData['order_id']);
		$order->setData('ext_customer_id',$mpOrderData['customer_id']);
		$order->save();
		return $order->getIncrementId();
    }
}  
?>


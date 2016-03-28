<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    PagSeguro
 * @copyright  Copyright (c) 2008 WebLibre (http://www.weblibre.com.br) - Guilherme Dutra (godutra@gmail.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 * PagSeguro Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class PagSeguro_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	//changing the payment to different from cc payment type and pagseguro payment type
	const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
	const PAYMENT_TYPE_SALE = 'SALE';
	protected $_code  = 'pagseguro_standard';
	protected $_formBlockType = 'pagseguro/standard_form';

	protected $_canUseInternal = true;
	protected $_canUseForMultishipping = false;
	protected $_canCapture = true;

	/**
	 * Get pagseguro session namespace
	 *
	 * @return PagSeguro_Model_Session
	 */
	public function getSession() {
		return Mage::getSingleton('pagseguro/session');
	}
	/**
	 * Get checkout session namespace
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	public function getCheckout() {
		return Mage::getSingleton('checkout/session');
	}
	/**
	 * Get current quote
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	public function getQuote() {
		return $this->getCheckout()->getQuote();
	}
	public function createFormBlock($name) {
		$block = $this->getLayout()->createBlock('pagseguro/standard_form', $name)
		->setMethod('pagseguro_standard')
		->setPayment($this->getPayment())
		->setTemplate('PagSeguro/standard/form.phtml');
		return $block;
	}
	public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment) {
		return $this;
	}
	public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment) {
	}
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('pagseguro/standard/redirect', array('_secure' => true));
	}
	function formatNumber ($number) {
		if ( $this->getVersion()==2 )
			return sprintf('%.2f', (double) $number);
		else
			return sprintf('%.2f', (double) $number) * 100;
	}
	function _trataTelefone($tel) {
		$numeros = preg_replace('/\D/','',$tel);
		$tel     = substr($numeros,sizeof($numeros)-9);
		$ddd     = substr($numeros,sizeof($numeros)-11,2);
		return array($ddd, $tel);
	}
	private function _endereco($endereco) {
		require_once(dirname(__FILE__).'/trata_dados.php');
		return TrataDados::trataEndereco($endereco);
	}
	public function getStandardCheckoutFormFields() {
		//$orderId = $this->getCheckout()->getLastOrderId();
		$retryOrderId = Mage::app()->getRequest()->getParam('retryOrder');
		$orderId = $retryOrderId? $retryOrderId : $this->getCheckout()->getLastRealOrderId();
		//$order   = Mage::getModel('sales/order')->load($orderId);
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

		$isOrderVirtual = $order->getIsVirtual();
		//$a = $order->getIsNotVirtual() ? $order->getShippingAddress() : $order->getShippingAddress();
		$a = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();

		$currency_code = $order->getBaseCurrencyCode();
		// Fazendo o telefone
		list($ddd, $telefone) = $this->_trataTelefone($a->getTelephone());
		// Dados de endereço (Endereço)
		list($endereco, $numero, $complemento) = $this->_endereco($a->getStreet(1).' '.$a->getStreet(2));
		// Dados de endereço (CEP)
		$cep = preg_replace('@[^\d]@', '', $a->getPostcode());

		$storeId = $this->getCheckout()->getPaypalStandardStoreId()?
		$this->getCheckout()->getPaypalStandardStoreId():
		$this->getQuote()->getStoreId();

		// Montando os dados para o formulário
		$sArr = array(
				'encoding'          => 'UTF-8',//'ISO-8859-1',
                'Tipo'              => "CP",
				$this->getVersion()==2?'receiverEmail':'email_cobranca'    			=> $this->getConfigData('emailID',$storeId),
                $this->getVersion()==2?'currency':'Moeda'             				=> $currency_code,
                $this->getVersion()==2?'reference':'ref_transacao'     				=> $order->getRealOrderId(),
                $this->getVersion()==2?'senderName':'cliente_nome'      			=> $a->getFirstname().' '.$a->getLastname(),
                $this->getVersion()==2?'shippingAddressPostalCode':'cliente_cep'    => $cep,
                $this->getVersion()==2?'shippingAddressStreet':'cliente_end'		=> $endereco,
                $this->getVersion()==2?'shippingAddressNumber':'cliente_num'		=> $numero,
                $this->getVersion()==2?'shippingAddressComplement':'cliente_compl'	=> (($a->getStreet3() !=='' || $a->getStreet4() !=='')? $a->getStreet3(): $a->getStreet2()),
                $this->getVersion()==2?'shippingAddressDistrict':'cliente_bairro'	=> ($a->getStreet4() !==''? $a->getStreet4(): $a->getStreet3()),
                $this->getVersion()==2?'shippingAddressCity':'cliente_cidade'    	=> $a->getCity(),
                $this->getVersion()==2?'shippingAddressState':'cliente_uf'        	=> $a->getRegionCode(),
                $this->getVersion()==2?'shippingAddressCountry':'cliente_pais'      => $a->getCountry(),
                $this->getVersion()==2?'senderAreaCode':'cliente_ddd'       		=> $ddd,
                $this->getVersion()==2?'senderPhone':'cliente_tel'       			=> $telefone,
                $this->getVersion()==2?'senderEmail':'cliente_email'    			=> $order->getCustomerEmail(),
		);

		$i = 1;
		$items = $order->getAllVisibleItems();
		$firstItemQty = 0;
		$shipping_amount = $order->getBaseShippingAmount();
		$tax_amount = $order->getBaseTaxAmount();
		$discount_amount = $order->getBaseDiscountAmount();

		$priceGrouping = $this->getConfigData('price_grouping', $order->getStoreId());
		if ($priceGrouping) {

			$order_total = $order->getBaseSubtotal() + $tax_amount + $discount_amount;
			$item_descr = $order->getStoreName(2) . " - Pedido " . $order->getRealOrderId();
			$item_price = $this->formatNumber($order_total);
			$sArr = array_merge($sArr, array(
                ($this->getVersion()==2?'itemDescription':'item_descr_').$i   	=> substr($item_descr, 0, 100),
                ($this->getVersion()==2?'itemId':'item_id_').$i      			=> $order->getRealOrderId(),
                ($this->getVersion()==2?'itemQuantity':'item_quant_').$i   		=> 1,
                ($this->getVersion()==2?'itemAmount':'item_valor_').$i   		=> $item_price,
                ($this->getVersion()==2?'itemShippingCost':'item_frete_').$i  	=> $shipping_amount,
                $this->getVersion()==2?'shippingType':'tipo_frete' 				=> $this->getVersion()==2?'3':''
			));
			$i++;

		} else {
			if ($items) {
				foreach($items as $item) {
					$item_price = 0;
					$item_qty = $item->getQtyToShip();
					if ( $firstItemQty==0 )
						$firstItemQty = $item_qty;

					if ($children = $item->getChildrenItems()) {
						foreach ($children as $child) {
							$item_price += $child->getBasePrice() * $child->getQtyOrdered() / $item_qty;
						}
						$item_price = $this->formatNumber($item_price);
					}
					if (!$item_price || $item_price <= 0) {
						$item_price = $this->formatNumber($item->getBasePrice());
					}
					$sArr = array_merge($sArr, array(
	                        ($this->getVersion()==2?'itemDescription':'item_descr_').$i   	=> substr($item->getName(), 0, 100),
	                        ($this->getVersion()==2?'itemId':'item_id_').$i      			=> substr($item->getSku(), 0, 100),
	                        ($this->getVersion()==2?'itemQuantity':'item_quant_').$i   		=> $item_qty,
	                        ($this->getVersion()==2?'itemWeight':'item_peso_').$i    		=> round($item->getWeight()), 
	                        ($this->getVersion()==2?'itemAmount':'item_valor_').$i   		=> $item_price,
					));
					$i++;
				}
			}

			if ($tax_amount > 0) {
				$tax_amount = $this->formatNumber($tax_amount);
				$sArr = array_merge($sArr, array(
                    ($this->getVersion()==2?'itemDescription':'item_descr_').$i   => "Taxa",
                    ($this->getVersion()==2?'itemId':'item_id_').$i      => "taxa",
                    ($this->getVersion()==2?'itemQuantity':'item_quant_').$i   => 1,
                    ($this->getVersion()==2?'itemAmount':'item_valor_').$i   => $tax_amount,
				));
				$i++;
			}

			if ($discount_amount != 0) {
				$discount_amount = $this->formatNumber($discount_amount);
				if (preg_match("/^1\.[23]/i", Mage::getVersion())) {
					$discount_amount = -$discount_amount;
				}
				$sArr = array_merge($sArr, array(
                    $this->getVersion()==2?'extraAmount':'extras'   => $discount_amount,
				));
			}

			$module = new PagSeguro_Model_Carrier_ShippingMethod();
			$psShippingMethodActive = $module->getConfigData('active',$storeId);

			if ($shipping_amount > 0) {
				$shipping_amount = $this->formatNumber($shipping_amount);
				$shippingPrice = $this->getConfigData('shipping_price', $order->getStoreId());
				switch ($shippingPrice) {
					case 'product':
						// passa o valor do frete como um produto
						$sArr = array_merge($sArr, array(
	                        ($this->getVersion()==2?'itemDescription':'item_descr_').$i => substr($order->getShippingDescription(), 0, 100),
	                        ($this->getVersion()==2?'itemId':'item_id_').$i      		=> "frete",
	                        ($this->getVersion()==2?'itemQuantity':'item_quant_').$i   	=> 1,
	                        ($this->getVersion()==2?'itemAmount':'item_valor_').$i   	=> $shipping_amount,
	                        $this->getVersion()==2?'shippingType':'tipo_frete' 			=> $this->getVersion()==2?'3':''
						));
						$i++;
						break;

					case 'separated':
					default:
						// passa o valor do frete separadamente
						//$sArr = array_merge($sArr, array($this->getVersion()==2?'itemShippingCost1':'item_frete_1' => $this->formatNumber($shipping_amount/$firstItemQty) ));
						$sArr = array_merge($sArr, array($this->getVersion()==2?'itemShippingCost1':'item_frete_1' => $this->formatNumber($shipping_amount) ));

						if ( $psShippingMethodActive ){
							if ( $order->_data['shipping_method']=='pagseguro_pagseguro:Sedex' )
								$e = ($this->getVersion()==2?'2':'SD');
							else
								$e = ($this->getVersion()==2?'1':'EN');
						}else{
							$e = $this->getVersion()==2?'3':'';
						}
						$sArr = array_merge($sArr, array($this->getVersion()==2?'shippingType':'tipo_frete' => $e ));

				}
			}
		}

		//$transaciton_type = $this->getConfigData('transaction_type',$storeId);
		//$totalArr = $a->getTotals();

		$sReq = '';
		$rArr = array();
		foreach ($sArr as $k=>$v) {
			/*
			 replacing & char with and. otherwise it will break the post
			 */
			$value =  str_replace("&","and",$v);
			$rArr[$k] =  $value;
			$sReq .= '&'.$k.'='.$value;
		}
			
		return $rArr;
	}
	//  define a url do pagseguro
	public function getPagSeguroUrl() {
		$url=$this->getConfigData('checkout_url');
		return $url;
	}
	
	//  define a url do pagseguro
	public function getVersion() {
		$url = $this->getConfigData('checkout_url');
		if ( strpos($url, '/v' )>0 )
			return substr( $url,strpos($url, '/v' )+2, 1 );
		else
			return 1;
		return $url;
	}
	
	//  define a url do pagseguro
	public function getPagSeguroStatusUrl() {
		$url=$this->getConfigData('status_url');
		return $url;
	}
	
    /**
     * getPagSeguroBoletoUrl
     * 
     * Retorna a URL para emissão de boleto do PagSeguro
     * 
	 * @param string $transactionId ID da transação PagSeguro
     * 
	 * @return string
     */ 
    public function getPagSeguroBoletoUrl($transactionId, $escapeHtml = true)
    {
		$url=$this->getConfigData('boleto_url');
        $url .= '&code=' . $transactionId;
        if ($escapeHtml) {
            $url = Mage::helper("pagseguro")->escapeHtml($url);
        }
    }

	public function getDebug() {
		return Mage::getStoreConfig('pagseguro/wps/debug_flag');
	}

	public function ipnPostSubmit() {
		$sReq = '';
		foreach($this->getIpnFormData() as $k=>$v) {
			$sReq .= '&'.$k.'='.urlencode(stripslashes($v));
		}
		//append ipn commdn
		$sReq .= "&cmd=_notify-validate";
		$sReq = substr($sReq, 1);
		if ($this->getDebug()) {
			$debug = Mage::getModel('pagseguro/api_debug')
			->setApiEndpoint($this->getPagSeguroUrl())
			->setRequestBody($sReq)
			->save();
		}
		$http = new Varien_Http_Adapter_Curl();
		$http->write(Zend_Http_Client::POST,$this->getPagSeguroUrl(), '1.1', array(), $sReq);
		$response = $http->read();
		$response = preg_split('/^\r?$/m', $response, 2);
		$response = trim($response[1]);
		if ($this->getDebug()) {
			$debug->setResponseBody($response)->save();
		}
		//when verified need to convert order into invoice
		$id = $this->getIpnFormData('invoice');
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($id);
		if ($response=='VERIFIED') {
			if (!$order->getId()) {
				/*
				 * need to have logic when there is no order with the order id from pagseguro
				 */
			} else {
				if ($this->getIpnFormData('mc_gross')!=$order->getGrandTotal()) {
					//when grand total does not equal, need to have some logic to take care
					$order->addStatusToHistory(
					$order->getStatus(),//continue setting current order status
					Mage::helper('pagseguro')->__('Order total amount does not match pagseguro gross total amount')
					);
				} else {
					/*
					 //quote id
					 $quote_id = $order->getQuoteId();
					 //the customer close the browser or going back after submitting payment
					 //so the quote is still in session and need to clear the session
					 //and send email
					 if ($this->getQuote() && $this->getQuote()->getId()==$quote_id) {
					 $this->getCheckout()->clear();
					 $order->sendNewOrderEmail();
					 }
					 */
					/*
					 if payer_status=verified ==> transaction in sale mode
					 if transactin in sale mode, we need to create an invoice
					 otherwise transaction in authorization mode
					 */
					if ($this->getIpnFormData('payment_status')=='Completed') {
						if (!$order->canInvoice()) {
							//when order cannot create invoice, need to have some logic to take care
							$order->addStatusToHistory(
							$order->getStatus(),//continue setting current order status
							Mage::helper('pagseguro')->__('Error in creating an invoice')
							);
						} else {
							//need to save transaction id
							$order->getPayment()->setTransactionId($this->getIpnFormData('txn_id'));
							//need to convert from order into invoice
							$invoice = $order->prepareInvoice();
							$invoice->register()->capture();
							Mage::getModel('core/resource_transaction')
							->addObject($invoice)
							->addObject($invoice->getOrder())
							->save();
							$order->addStatusToHistory(
                                    'processing',//update order status to processing after creating an invoice
							Mage::helper('pagseguro')->__('Invoice '.$invoice->getIncrementId().' was created')
							);
						}
					} else {
						$order->addStatusToHistory(
						$order->getStatus(),
						Mage::helper('pagseguro')->__('Received IPN verification'));
					}
				}//else amount the same and there is order obj
				//there are status added to order
				$order->save();
			}
		}else {
			/*
			 Canceled_Reversal
			 Completed
			 Denied
			 Expired
			 Failed
			 Pending
			 Processed
			 Refunded
			 Reversed
			 Voided
			 */
			$payment_status= $this->getIpnFormData('payment_status');
			$comment = $payment_status;
			if ($payment_status == 'Pending') {
				$comment .= ' - ' . $this->getIpnFormData('pending_reason');
			} elseif ( ($payment_status == 'Reversed') || ($payment_status == 'Refunded') ) {
				$comment .= ' - ' . $this->getIpnFormData('reason_code');
			}
			//response error
			if (!$order->getId()) {
				/*
				 * need to have logic when there is no order with the order id from pagseguro
				 */
			} else {
				$order->addStatusToHistory(
				$order->getStatus(),//continue setting current order status
				Mage::helper('pagseguro')->__('PagSeguro IPN Invalid.'.$comment)
				);
				$order->save();
			}
		}
	}
}

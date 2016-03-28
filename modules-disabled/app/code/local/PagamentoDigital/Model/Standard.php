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
 * @package    Pagamento Digital
 * @copyright  Copyright (c) 2008 WebLibre (http://www.weblibre.com.br) - Guilherme Dutra (godutra@gmail.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PagamentoDigital Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class PagamentoDigital_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    //changing the payment to different from cc payment type and pagamentodigital payment type
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';

    protected $_code  = 'pagamentodigital_standard';
    protected $_formBlockType = 'pagamentodigital/standard_form';
    protected $_allowCurrencyCode = array('BRL');

     /**
     * Get pagamentodigital session namespace
     *
     * @return PagamentoDigital_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('pagamentodigital/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Using internal pages for input payment data
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return false;
    }

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('pagamentodigital/standard_form', $name)
            ->setMethod('pagamentodigital_standard')
            ->setPayment($this->getPayment())
            ->setTemplate('PagamentoDigital/standard/form.phtml');

        return $block;
    }

    /*validate the currency code is avaialable to use for pagamentodigital or not*/
    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('pagamentodigital')->__('Selected currency code ('.$currency_code.') is not compatabile with PagamentoDigital'));
        }
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
       return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }

    public function canCapture()
    {
        return true;
    }

    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('pagamentodigital/standard/redirect', array('_secure' => true));
    }

function getNumEndereco($endereco) {
    	$numEndereco = '';

    	//procura por vírgula ou traço para achar o final do logradouro
    	$posSeparador = $this->getPosSeparador($endereco, false);  
    	if ($posSeparador !== false) {
	    $numEndereco = trim(substr($endereco, $posSeparador + 1));
	}

    	//procura por vírgula, traço ou espaço para achar o final do número da residência
      	$posComplemento = $this->getPosSeparador($numEndereco, true);
	if ($posComplemento !== false) {
	    $numEndereco = trim(substr($numEndereco, 0, $posComplemento));
	}

	if ($numEndereco == '') {
	    $numEndereco = '?';
	}
	
	return($numEndereco);
}

function getPosSeparador($endereco, $procuraEspaco = false) {
  	  $posSeparador = strpos($endereco, ',');
      	  if ($posSeparador === false) {
	      $posSeparador = strpos($endereco, '-');
	  }

	  if ($procuraEspaco) {	  
	      if ($posSeparador === false) {
	          $posSeparador = strrpos($endereco, ' ');
	      }
	  }

	  return($posSeparador);
}

public function getStandardCheckoutFormFields() {
       	$a = $this->getQuote()->getShippingAddress();
       	$currency_code = $this->getQuote()->getBaseCurrencyCode();
			
		$cep = substr(eregi_replace ("[^0-9]", "", $a->getPostcode()).'00000000',0,8);
        // removes non numeric characters from the telephone field, and trims to 8 chars long.
        
        $cust_ddd = '00';    	
        $cust_telephone = eregi_replace ("[^0-9]", "", $a->getTelephone());
        $st = strlen($cust_telephone)-8;
        if ($st>0) { // in case this string is longer than 8 characters (PagamentoDigital's settings)
            $cust_ddd = substr($cust_telephone, 0, 2);
	    	$cust_telephone = substr($cust_telephone, $st, 8);
        }

        Mage::log('emailID=' . $this->getConfigData('emailID'));
       	$sArr = array(
            'email_loja'        =>  $this->getConfigData('emailID'),
            'tipo_integracao'   => "PAD",
    	    'id_pedido'     	=> $this->getCheckout()->getLastRealOrderId(),
            'nome'      		=> $a->getFirstname().' '.$a->getLastname(),
            'cep'       		=> $cep,
            'endereco'       	=> $a->getStreet(1),
            'complemento'     	=>  $a->getStreet(2),
            'bairro'    		=> "?",
            'cidade'    		=> $a->getCity(),
            'estado'        	=> $a->getState(),
            'pais'      		=> $a->getCountry(),
            'cliente_ddd'       => $cust_ddd,
            'telefone'       	=> $a->getTelephone(),
            'email'    			=> $a->getEmail(),
        );
		
		$items = $this->getQuote()->getAllItems();

        if ($items) {
            $i = 1;
            foreach($items as $item) {
                $valorProduto = ($item->getBaseCalculationPrice() - $item->getBaseDiscountAmount());
				$valorProduto = str_replace(',', '.', $valorProduto);
				
				$sArr = array_merge($sArr, array(
                    'produto_descricao_'.$i   => $item->getName(),
                    'produto_codigo_'.$i      => $item->getSku(),
                    'produto_qtde_'.$i   => $item->getQty(),
                    'produto_valor_'.$i   => $valorProduto,
			    ));
			}
	            
            if($item->getBaseTaxAmount()>0){
                $sArr = array_merge($sArr, array(
                'tax_'.$i      => sprintf('%.2f',$item->getBaseTaxAmount()),
                ));
            }
            $i++;
        }

        //$transaciton_type = $this->getConfigData('transaction_type');
        $totalArr = $a->getTotals();
        $shipping = sprintf('%.2f', $this->getQuote()->getShippingAddress()->getBaseShippingAmount());
		$shipping = str_replace(',', '.', $shipping);

		//passa o valor do frete total em uma única variavel para o pagamentodigital
    	$sArr = array_merge($sArr, array('frete' => $shipping));

		//passa a URL para o Pagamento Digital retornar à loja
		if ($this->getConfigData('retorno') != '') {
	    	$sArr = array_merge($sArr, array('url_retorno' => $this->getConfigData('retorno')));
	    	$sArr = array_merge($sArr, array('redirect' => 'true'));
		}

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

        if ($this->getDebug() && $sReq) {
            $sReq = substr($sReq, 1);
            $debug = Mage::getModel('pagamentodigital/api_debug')
                    ->setApiEndpoint($this->getPagamentoDigitalUrl())
                    ->setRequestBody($sReq)
                    ->save();
        }
		
        return $rArr;
    }

    //  define a url do pagamentodigital
    public function getPagamentoDigitalUrl()
    {
         $url='https://www.pagamentodigital.com.br/checkout/pay/';
         return $url;
    }

    public function getDebug()
    {
        return Mage::getStoreConfig('pagamentodigital/wps/debug_flag');
    }


    public function ipnPostSubmit()
    {
        $sReq = '';
        foreach($this->getIpnFormData() as $k=>$v) {
            $sReq .= '&'.$k.'='.urlencode(stripslashes($v));
        }
        //append ipn commdn
        $sReq .= "&cmd=_notify-validate";
        $sReq = substr($sReq, 1);

        if ($this->getDebug()) {
            $debug = Mage::getModel('pagamentodigital/api_debug')
                    ->setApiEndpoint($this->getPagamentoDigitalUrl())
                    ->setRequestBody($sReq)
                    ->save();
        }
        $http = new Varien_Http_Adapter_Curl();
        $http->write(Zend_Http_Client::POST,$this->getPagamentoDigitalUrl(), '1.1', array(), $sReq);
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
                * need to have logic when there is no order with the order id from pagamentodigital
                */

            } else {

                if ($this->getIpnFormData('mc_gross')!=$order->getGrandTotal()) {
                    //when grand total does not equal, need to have some logic to take care
                    $order->addStatusToHistory(
                        $order->getStatus(),//continue setting current order status
                        Mage::helper('pagamentodigital')->__('Order total amount does not match pagamentodigital gross total amount')
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
                                Mage::helper('pagamentodigital')->__('Error in creating an invoice')
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
                                Mage::helper('pagamentodigital')->__('Invoice '.$invoice->getIncrementId().' was created')
                           );
                       }
                    } else {
                        $order->addStatusToHistory(
                                $order->getStatus(),
                                Mage::helper('pagamentodigital')->__('Received IPN verification'));
                    }

                }//else amount the same and there is order obj
                //there are status added to order
                $order->save();
            }
        }else{
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
                * need to have logic when there is no order with the order id from pagamentodigital
                */
            } else {
                $order->addStatusToHistory(
                    $order->getStatus(),//continue setting current order status
                    Mage::helper('pagamentodigital')->__('PagamentoDigital IPN Invalid.'.$comment)
                );
                $order->save();
            }
        }
    }

}

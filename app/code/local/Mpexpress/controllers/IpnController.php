<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      AndrÃ© Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Mpexpress_IpnController extends Mage_Core_Controller_Front_Action{
    
    protected $_return = null;
    protected $_order = null;
    protected $_order_id = null;
    protected $_mpcartid = null;
    protected $_sendemail = false;
    protected $_hash = null;
	protected $_order_info = null;
	
	const TOPIC_ORDERS = "orders";
	const TOPIC_PAYMENTS = "payment";
	
	public function testAction(){
		$params = $this->getRequest()->getParams();	
		$ipn = Mage::getModel('mpexpress/Checkout');    
		echo $ipn->GetStatus($params['id']);
	}
	
    public function indexAction(){
		$params = $this->getRequest()->getParams();
		if ( count($params) == 0 ) {  //empty, so parse json in body
			$params = Mage::helper('core')->jsonDecode( $this->getRequest()->getRawBody() );
		}
		
		if ( !isset($params['topic']) ) {
			if ( isset($params['payer']) ) {
				$params['topic'] = self::TOPIC_PAYMENTS;
			}else{
				$this->getResponse()->setHeader('HTTP/1.0','500',true);
				return;
			}
		}
		if ( $params['topic'] == self::TOPIC_ORDERS ){ //MercadoLivre
		
			if ( isset($params['resource']) ){
				try {
					$res = explode('/',$params['resource']);
					$id = $res[2];
					$ipn = Mage::getModel('mpexpress/Quote'); 
					$this->_return = $ipn->GetOrder($id);
					if ( (int)$this->_return['id'] === (int)$id ) {
						$this->_process_order( $params['topic'] );						
					}
				} catch (Exception $e) {
					Mage::logException($e);
					$this->getResponse()->setHeader('HTTP/1.0','500',true);
				}
			}			
		}else if ( $params['topic'] == self::TOPIC_PAYMENTS ){ //MercadoPago
		
			if ( isset($params['id']) ){
				 try {
					$ipn = Mage::getModel('mpexpress/Checkout');    
					$retorno = $ipn->GetStatus($params['id']);
					$this->_return = $retorno['collection'];
			  
					 if ((int)$this->_return['id'] === (int)$params['id']) {
						$this->_process_order( $params['topic'] );
					
					}
				} catch (Exception $e) {
					Mage::logException($e);
					$this->getResponse()->setHeader('HTTP/1.0','500',true);
				}
			}
		}
    } 
    
    private function _process_order($topic){   
        $standard = Mage::getModel('mpexpress/Express');  
        $this->_get_order($topic);
        $config = Mage::getModel('mpexpress/Express');                         

        if($this->_sendemail){
           $name = $this->_return['payer']['first_name'].' ' .$this->_return['payer']['last_name'];
           $this->notify($name,$this->_return['payer']['email']);
        }
        if ( $topic == self::TOPIC_PAYMENTS ){ //MercadoPago
			$this->_return['TipoPagamento']	=	$this->_return['payment_type'];
			$this->_return['TransacaoID']	=	$this->_return['transaction_order_id'];
			$this->_return['sharing_amount']	=	$this->_return['net_received_amount'];
			
			Mage::log('Notification received by MercadoPago IPN. Topic:' . $topic . ' Order:' . $this->_order_id . ' TopicId:' . $this->_return['id']);
			$this->retornoMercadoPago($this->_order_id, 
								   $this->_return['status'], 
								   $this->_return );
		}else{
			//Atualização de impressão/envio/entrega/pagamento
			Mage::log('Received update from MercadoLivre/Pago. DUMMY. Topic:' . $topic . ' Order:' . $this->_order_id . ' TopicId:' . $this->_return['id']);
		}
    }
    
    private function _get_order($topic){
        if ( empty($this->_order) || $this->_order == null ) {
            $idr = $topic==self::TOPIC_ORDERS ? $this->_return['id'] : $this->_return['external_reference'];
            $ida = explode('-',$idr);
            $this->_hash  = count($ida)==2 ? $ida[1]: null;
            
            /// if is normal checkout (order is already created)
            if ($ida[0] == 'mpexpress'){               
				$preorder = Mage::getModel('sales/order')->loadByIncrementId($this->_hash); 
				if (isset($preorder['increment_id'])){
					$this->_order = $preorder;
				}else{
				   echo 'Order not found';
				   die;            
				}
            } else {// else, if is checkout express, maybe order is not created            
                
                $mpcart = Mage::getModel('mpexpress/mpcart')->load($this->_hash,'hash');  
                $this->_order_id = $mpcart->getOrderId();
                $this->_mpcartid = $mpcart->getMpexpressCartId();

                // If donÂ´t have order, generate a order and send email
     
				if( (is_null($this->_order_id) || empty($this->order_id)) and !is_null($this->_mpcartid)){    
					$this->_order_id = $mpcart->generateEmptyOrder($this->_mpcartid);  
					$preorder = Mage::getModel('sales/order')->loadByIncrementId($this->_order_id); 
				
					if (isset($preorder['increment_id'])){
						$this->_order = $preorder;
					}else{
						echo 'Order not found';
						die;            
					}
					//$this->_sendemail = true;
				}else{  
					//Search by ext_order_id
					$order = Mage::getModel('sales/order')->loadByAttribute('ext_order_id',$idr); 
					if ( isset($order['increment_id']) ){
						$this->_order = $order;
						$this->_order_id = $order->getIncrementId();
					}else{ //Create order from MercadoPago IPN or Webhook request
						if ( $topic==self::TOPIC_ORDERS ){
							$quote = Mage::getModel('mpexpress/Quote');
							$quote->importOrderData( $this->_return );
							$this->_order_id = $quote->save();
							Mage::log('Order created by MercadoLivre IPN. Topic:' . $topic . ' IncrementId:' . $this->_order_id . ' Origin:' . $idr);
							$this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_order_id); 
						}else{
							Mage::throwException('Not found the order to receive notifications. Topic:' . $topic . ' Id:' . $idr);
						}
					}
				}
			}
		}
	}

    private function notify($sendToName, $sendToEmail) {   
		$store = Mage::app()->getStore();
		$store->getName();
		$link = '<a href="'. Mage::getBaseUrl() . 'mpexpress/information/address/hash/'.$this->_hash.'">'.Mage::getBaseUrl().'mpexpress/information/address/hash/'.$this->_hash.'</a>';
		$name = Mage::getStoreConfig('general/store_information/name');
		$from = Mage::getStoreConfig('trans_email/ident_general/email');
		$subject = Mage::helper('mpexpress')->__('Complete your order shipping information');
		$charset = '<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />';
		$dear = Mage::helper('mpexpress')->__('Thank you for your purchase.');
		$linha = ' <br /> ';        
		$information = Mage::helper('mpexpress')->__('To complete your order, if is not done yet, fill the address information at the address below.');        
		$finalmessage = $charset.$dear.$linha.$linha.$information.$linha.$linha.$link;
		$mail = Mage::getModel('core/email');
		$mail->setToName($sendToName);
		$mail->setToEmail($sendToEmail);
		$mail->setBody($finalmessage);
		$mail->setSubject('=?utf-8?B?'.base64_encode($subject).'?=');
		$mail->setFromEmail($from);
		$mail->setFromName($name);
		$mail->setType('html');
	 
		try {
			$mail->send();
		}
		catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
		return true;
	}

	private function retornoMercadoPago($referencia, $status, $post){
		$salesOrder = Mage::getSingleton('sales/order');
		$order = $salesOrder->loadByIncrementId($referencia);
		$sendmail = false;

		if ($order->getId()) {
			
			if (in_array(strtolower(trim($status)), array('pending', 'in_process', 'approved'))) {
				// Envia email de confirmaÃ§Ã£o ao cliente
				if( !$order->getEmailSent() && $sendmail ) {
					$order->sendNewOrderEmail();
					$order->setEmailSent($sendmail);
				}
			}
			
			// Verificando o Status passado pelo PagSeguro
			if (in_array(strtolower($status), array('approved'))) {
				if ($order->canUnhold()) {
					$order->unhold();
				}
				
				if ($order->canInvoice()) {
					$invoice_msg = utf8_encode(sprintf('Pagamento confirmado (%s). Transação MercadoPago: %s.', $post['TipoPagamento'], $post['TransacaoID']));

					$invoice = $order->prepareInvoice();
					$invoice->register()->pay();
					$invoice->addComment($invoice_msg, $sendmail);
					$invoice->setEmailSent($sendmail);
					Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder())
					->save();

					$invoice_msg = utf8_encode(sprintf('Pagamento confirmado (%s). Transação MercadoPago: %s. Fatura #%s criada.', $post['TipoPagamento'], $post['TransacaoID'], $invoice->getIncrementId()));
					if ( $sendmail )
						$invoice->sendEmail(true, $invoice_msg);
					$order->getPayment()->setTransactionId($post['TransacaoID']);
					$order->getPayment()->setIsTransactionClosed( (double)$post['total_paid_amount'] == $order->getGrandTotal() );
					$order->getPayment()->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, array('code'=>$post['id'],'value'=>$post['total_paid_amount']));	
					
					$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, $invoice_msg, $notified = $sendmail);
					$history = $order->getStatusHistoryById(Mage_Sales_Model_Order::STATE_PROCESSING);
					if ( $history )
						$history->setIsVisibleOnFront(true);
				} else {
					$releaseDt = new DateTime($post['money_release_date']);
					$now = new DateTime();
					if( $releaseDt > $now ){
						$order->getPayment()->setTransactionId($post['TransacaoID']);
						if ( $order->hasShipments() ){
							$order->addStatusToHistory('sharing', 'Pagamento liberado para saque', $notified = false);
						}else{
							$order->addStatusHistoryComment('Pagamento dispon&iacute;vel para saque', 'payment_available')
									->setIsCustomerNotified(false)
									->setIsVisibleOnFront(false);
						}
					}else
						return;
				}
			} else {
				// Não está completa, vamos processar...
				$comment_add = false;
				$comment = $status;
				$notified = false;
				$changeTo = $order->getStatus();

				if (in_array(strtolower(trim($status)), array('cancelled', 'refunded', 'rejected'))) {
					$comment_add = true;
					if (strtolower(trim($status)) == 'refunded') {
						$comment = utf8_encode('Estornado pelo MercadoPago. Será realizada a devolução do valor pago na conta MercadoPago do cliente.');
						foreach ($order->getAllStatusHistory() as $status) {
							if (strpos($status->getComment(), $comment) !== false) {
								$comment_add = false;
								break;
							}
						}

					}else if (strtolower(trim($status)) == 'rejected') {
						$comment = utf8_encode('Cancelado pelo MercadoPago (negado ou chargeback do cartão)');
					}else {
						$comment = utf8_encode('Cancelado pelo MercadoPago (pagamento não realizado)');
					}
					// Pedido cancelado
					if ($order->canUnhold()) {
						$order->unhold();
					}
					if ($order->canCancel()) {
						$order->getPayment()->setMessage($comment);
						$order->cancel();
						if ( $sendmail ) {
							$order->sendOrderUpdateEmail(true, $comment);
							$notified = true;
						}
					}
				} elseif ( strtolower(trim($status))=='in_process' ) {
					// Pagamento aprovado, em análise pelo PagSeguro
					if ($order->canUnhold()) {
						$order->unhold();
					}
					$comment .= ($post['TipoPagamento']? ' - ':'') . $post['TipoPagamento'] . ' / Pagamento efetuado em análise pelo MercadoPago';
					//$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, utf8_encode($comment), true);
				} elseif ( strtolower(trim($status))=='pending' ) {
					$comment .= ($post['TipoPagamento']? ' - ':'') . $post['TipoPagamento'] . ' / Aguardando confirmação de pagamento pelo MercadoPago';
					$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $comment, $notified = $sendmail);
				} else {
					// Status desconhecido (Segurar)
					if ( $order->canHold() ) {
						$changeTo = Mage_Sales_Model_Order::STATE_HOLDED;
						$comment .= ' - MercadoPago enviou código desconhecido (verificar)';
						$order->hold();
						$comment_add = true;
					}
				}
				
				if ( $comment_add ) {
			        $order->addStatusHistoryComment(utf8_encode($comment), false)
					            ->setIsCustomerNotified($notified)
					            ->setIsVisibleOnFront($notified);
					Mage::log(utf8_encode('Pedido '.$order->getId().' Histórico: ' . $changeTo . ' - ' . $comment . $notified?' (notificado)':''));
				}
			}

			$order->save();
		}

	}
	
    private function getOrderStoreId($orderId) {
        return Mage::getModel('sales/order')->load($orderId)->getStoreId();
    }
}

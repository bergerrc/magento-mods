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
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PagSeguro Standard Checkout Controller
 *
 * @author      Michael Granados <mike@visie.com.br>
 */
class PagSeguro_StandardController
extends Mage_Core_Controller_Front_Action
{
	/**
	 * Order instance
	 */
	protected $_order;

	/**
	 *  Get order
	 *
	 *  @param    none
	 *  @return	  Mage_Sales_Model_Order
	 */
	public function getOrder()
	{
		if ($this->_order == null) {
		}
		return $this->_order;
	}

	/**
	 * Get singleton with pagseguro strandard order transaction information
	 *
	 * @return PagSeguro_Model_Standard
	 */
	public function getStandard()
	{
		return Mage::getSingleton('pagseguro/standard');
	}

	/**
	 * When a customer chooses Paypal on Checkout/Payment page
	 *
	 */
	public function redirectAction()
	{
		$session = Mage::getSingleton('checkout/session');
		if ( $session->getQuoteId() )
		$session->setPaypalStandardQuoteId( $session->getQuoteId() );
		if ( $session->getStoreId() )
		$session->setPaypalStandardStoreId( $session->getStoreId() );
		$this->getResponse()->setHeader("Content-Type", "text/html; charset=ISO-8859-1", true);
		$this->getResponse()->setBody($this->getLayout()->createBlock('pagseguro/standard_redirect')->toHtml());
		$session->unsQuoteId();
		$session->setStoreId(null);
	}

	/**
	 * Retorno dos dados feito pelo PagSeguro
	 */
	public function obrigadoAction()
	{
		$standard = $this->getStandard();
		# É um $_GET, trate normalmente
		if (!$this->getRequest()->isPost()) {
			$session = Mage::getSingleton('checkout/session');
			$session->setQuoteId($session->getPaypalStandardQuoteId(true));
			/**
			 * set the quote as inactive after back from pagseguro
			 */
			Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
			/**
			 * send confirmation email to customer
			 */
			$order = Mage::getModel('sales/order');
			$order->load(Mage::getSingleton('checkout/session')->getLastOrderId());

			// Envia email de confirmaÃ§Ã£o ao cliente
			if( $order->getId() && !$order->getEmailSent() ) {
				$order->sendNewOrderEmail();
				$order->setEmailSent(true);
				$order->save();
			}

			$storeId = $order->getStoreId();

			if ($standard->getConfigData('use_return_page_cms', $storeId)) {
				$url = $standard->getConfigData('return_page_cms', $storeId);
			} else {
				$url = $standard->getConfigData('retorno',$storeId);
			}
			Mage::log($standard->getCode() . ' - Retornando para URL: ' . $url);
			$this->_redirect($url);
		} else {
			// Vamos ao retorno automático
			if (!defined('RETORNOPAGSEGURO_NOT_AUTORUN')) {
				define('RETORNOPAGSEGURO_NOT_AUTORUN', true);
				define('PAGSEGURO_AMBIENTE_DE_TESTE', true);
			}
			// Incluindo a biblioteca escrita pela Visie
			include_once(dirname(__FILE__).'/retorno.php');
			// Brincanco com a biblioteca
			$ret = RetornoPagSeguro::verifica($_POST, false, array($this, 'retornoPagSeguro'))?'OK':'NOK';
			Mage::log($standard->getCode() . ' - Verificação da Transação: ' . $ret);
		}
	}

	public function retornoPagSeguro($referencia, $status, $valorFinal, $produtos, $post)
	{
		$salesOrder = Mage::getSingleton('sales/order');
		$order = $salesOrder->loadByIncrementId($referencia);

		if ($order->getId()) {
			
			if (in_array(strtolower(trim($status)), array('aguardando pagto', 'em análise', 'aprovado'))) {
				// Envia email de confirmaÃ§Ã£o ao cliente
				if( !$order->getEmailSent() ) {
					$order->sendNewOrderEmail();
					$order->setEmailSent(true);
				}
			}
			
			// Verificando o Status passado pelo PagSeguro
			if (in_array(strtolower($status), array('completo', 'aprovado'))) {
				if ($order->canUnhold()) {
					$order->unhold();
				}
				
				if ($order->canInvoice()) {
					$invoice_msg = utf8_encode(sprintf('Pagamento confirmado (%s). Transação PagSeguro: %s.', $post->TipoPagamento, $post->TransacaoID));

					$invoice = $order->prepareInvoice();
					$invoice->register()->pay();
					$invoice->addComment($invoice_msg, true);
					$invoice->setEmailSent(true);
					Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder())
					->save();

					$invoice_msg = utf8_encode(sprintf('Pagamento confirmado (%s). Transação PagSeguro: %s. Fatura #%s criada.', $post->TipoPagamento, $post->TransacaoID, $invoice->getIncrementId()));
					$invoice->sendEmail(true, $invoice_msg);
					$order->getPayment()->setTransactionId($post->TransacaoID);
					$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, $invoice_msg, $notified = true);
					$history = $order->getStatusHistoryById(Mage_Sales_Model_Order::STATE_PROCESSING);
					if ( $history )
						$history->setIsVisibleOnFront(true);
				} else {
					$order->getPayment()->setTransactionId($post->TransacaoID);
					if ( $order->hasShipments() ){
						$order->addStatusToHistory('sharing', 'Pagamento liberado para saque', $notified = false);
					}else{
						$order->addStatusHistoryComment('Pagamento dispon&iacute;vel para saque', 'payment_available')
					            ->setIsCustomerNotified(false)
					            ->setIsVisibleOnFront(false);
					}
				}
			} else {
				// Não está completa, vamos processar...
				$comment_add = false;
				$comment = $status;
				$notified = false;
				$changeTo = $order->getStatus();

				if (in_array(strtolower(trim($status)), array('cancelado', 'devolvido'))) {
					$comment_add = true;
					if (strtolower(trim($status)) == 'devolvido') {
						$comment = utf8_encode('Cancelado pelo PagSeguro. Será realizada a devolução do valor pago.');
						foreach ($order->getAllStatusHistory() as $status) {
							if (strpos($status->getComment(), $comment) !== false) {
								$comment_add = false;
								break;
							}
						}

					} else {
						$comment = utf8_encode('Cancelado pelo PagSeguro (pagamento não realizado, negado ou chargeback do cartão)');
					}
					// Pedido cancelado
					if ($order->canUnhold()) {
						$order->unhold();
					}
					if ($order->canCancel()) {
						$order->getPayment()->setMessage($comment);
						$order->cancel();
						$order->sendOrderUpdateEmail(true, $comment);
						$notified = true;
					}
				} elseif ( strtolower(trim($status))=='em análise' ) {
					// Pagamento aprovado, em análise pelo PagSeguro
					if ($order->canUnhold()) {
						$order->unhold();
					}
					$comment .= ($post->TipoPagamento? ' - ':'') . $post->TipoPagamento . ' / Pagamento efetuado em análise pelo PagSeguro';
					//$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, utf8_encode($comment), true);
				} elseif ( strtolower(trim($status))=='aguardando pagto' ) {
					$comment .= ($post->TipoPagamento? ' - ':'') . $post->TipoPagamento . ' / Aguardando confirmação de pagamento do PagSeguro';
					$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $comment, $notified = false);
				} else {
					// Status desconhecido (Segurar)
					if ( $order->canHold() ) {
						$changeTo = Mage_Sales_Model_Order::STATE_HOLDED;
						$comment .= ' - PagSeguro enviou código desconhecido (verificar)';
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
	
    /**
     * Retorna ID da store, atravÃ©s do ID do pedido
     * 
     */
    function getOrderStoreId($orderId) {
        return Mage::getModel('sales/order')->load($orderId)->getStoreId();
    }

	/**
	 * Retorna bloco de parcelamento de acordo
	 * com a mudanÃ§a de preÃ§o do produto.
	 *
	 */
	public function installmentsAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
    public function indexAction()
    {
    	echo "TESTE OK";
    }
}

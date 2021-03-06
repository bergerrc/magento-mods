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
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Paypal Standard Checkout Controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class PagamentoDigital_StandardController extends Mage_Core_Controller_Front_Action
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

    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with pagamentodigital strandard order transaction information
     *
     * @return PagamentoDigital_Model_Standard
     */
    public function getStandard()
    {
        return Mage::getSingleton('pagamentodigital/standard');
    }

    /**
     * When a customer chooses Paypal on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setPaypalStandardQuoteId($session->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock('pagamentodigital/standard_redirect')->toHtml());
        $session->unsQuoteId();
    }

    /**
     * When a customer cancel payment from pagamentodigital.
     */
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));

        // cancel order
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
        }

        /*we are calling getPaypalStandardQuoteId with true parameter, the session object will reset the session if parameter is true.
        so we don't need to manually unset the session*/
        //$session->unsPaypalStandardQuoteId();

        //need to save quote as active again if the user click on cacanl payment from pagamentodigital
        //Mage::getSingleton('checkout/session')->getQuote()->setIsActive(true)->save();
        //and then redirect to checkout one page
        $this->_redirect('checkout/cart');
     }

    /**
     * when pagamentodigital returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function  successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));
        /**
         * set the quote as inactive after back from pagamentodigital
         */
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

        /**
         * send confirmation email to customer
         */
        $order = Mage::getModel('sales/order');

        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
        if($order->getId()){
            $order->sendNewOrderEmail();
        }

        //Mage::getSingleton('checkout/session')->unsQuoteId();

        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }

    /**
     * when pagamentodigital returns via ipn
     * cannot have any output here
     * validate IPN data
     * if data is valid need to update the database that the user has
     */
    public function ipnAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('');
            return;
        }

        if($this->getStandard()->getDebug()){
            $debug = Mage::getModel('pagamentodigital/api_debug')
                ->setApiEndpoint($this->getStandard()->getPaypalUrl())
                ->setRequestBody(print_r($this->getRequest()->getPost(),1))
                ->save();
        }

        $this->getStandard()->setIpnFormData($this->getRequest()->getPost());
        $this->getStandard()->ipnPostSubmit();
    }
}

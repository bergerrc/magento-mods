<?php

/**
 * One page checkout status
 *
 * @category   Indexa
 * @package    Indexa_Fretefacil
 * @author     Indexa Team -> desenvolvimento [at] indexainternet [dot] com [dot] br
 * @copyright  Copyright (c) 2011 Indexa
 */
class Indexa_Fretefacil_Model_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage {
   
    /**
     * Specify quote payment method
     *
     * @param   array $data
     * @return  array
     */
    public function savePayment($data)
    { 
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
        }
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        $shipping = $this->getQuote()->getShippingAddress();
        /* @var Mage_Sales_Model_Quote_Address $shipping */        
        $shippingMethod = $shipping->getShippingMethod();

        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $payment = $quote->getPayment();
        /* @var Mage_Sales_Model_Quote_Payment $payment */        
        $payment->importData($data);

        /**
         * define payment methods to check
         */
        $paypal_methods[] = 'paypal_billing_agreement';
	$paypal_methods[] = 'hosted_pro';
	$paypal_methods[] = 'payflow_link';
        $paypal_methods[] = 'verisign';
        $paypal_methods[] = 'paypal_express';
        $paypal_methods[] = 'paypal_direct';
        $paypal_methods[] = 'paypaluk_direct';
        $paypal_methods[] = 'paypaluk_express';
        $paypal_methods[] = 'paypal_standard';
        
        /**
         * check if fretefacil is selected as shipping method 
         */
        if( Indexa_Fretefacil_Model_Carrier_Indexafretefacil::SHIPPING_METHOD_CODE == $shippingMethod && !in_array( $payment->getMethod(), $paypal_methods ) ){
                return array('error' => Mage::getStoreConfig('carriers/indexafretefacil/error_payment'), 'message' => Mage::getStoreConfig('carriers/indexafretefacil/error_payment') );
        }
        
        $quote->save();

        $this->getCheckout()
            ->setStepData('payment', 'complete', true)
            ->setStepData('review', 'allow', true);

        return array();
    }
}

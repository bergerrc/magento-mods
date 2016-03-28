<?php


class Mpexpress_Helper_Observer
{
   public function filterpaymentmethod(Varien_Event_Observer $observer) {


     /* call get payment method */
     $method = $observer->getEvent()->getMethodInstance();
     $quote = $observer->getEvent()->getQuote();

     if($method->getCode()=='mpexpress'){

	 if(Mage::getSingleton('customer/session')->isLoggedIn() or ($quote!=null and $quote->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST )) {

	   $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
	   $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
	   $quote = $observer->getEvent()->getQuote();

//Mage::log($method->getCode() . ' role:' . $roleId. ' group:' . $quote->getCustomerGroupId());

          if($roleId == 5 or ($quote != null and $quote->getData('customer_group_id') == 5)){
            $result = $observer->getEvent()->getResult();   
            $result->isAvailable = true;
	     return;
          }
  	 }

        $result = $observer->getEvent()->getResult();   
        $result->isAvailable = false;
	 return;
     }

     if($method->getCode()=='pagseguro_standard'){

	 if(Mage::getSingleton('customer/session')->isLoggedIn() or ($quote != null and $quote->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST )) {

	   $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
	   $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
	   $quote = $observer->getEvent()->getQuote();

          if($roleId == 5 or ( $quote != null and $quote->getData('customer_group_id') ==5)){
            $result = $observer->getEvent()->getResult();   
            $result->isAvailable = false;
	     return;
          }
  	 }

        $result = $observer->getEvent()->getResult();   
        $result->isAvailable = true;
	 return;
     }

   }
}
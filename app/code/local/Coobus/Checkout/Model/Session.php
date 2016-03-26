<?php



class Coobus_Checkout_Model_Session extends Mage_Checkout_Model_Session
{


    /**
     * Get checkout quote instance by current session
     *
     * @return Mage_Sales_Model_Quote
     
    public function getQuote()
    {
    	$q = parent::getQuote();
        if ( $q->hasItems() ) {
			Mage::helper("coobus_checkout")->setStoreByItems( $q );
        }
    	
    	return $q;
    }

    public function loadCustomerQuote()
    {
    	parent::loadCustomerQuote();
        if ( $this->_quote->hasItems() ) {
			Mage::helper("coobus_checkout")->setStoreByItems( $this->_quote );
        }

        return $this;
    }
    */
    
    public function getStoreId()
    {
        return $this->getData('store_' . $this->_getQuoteIdKey());
    }
    
    public function setStoreId($storeId)
    {
        $this->setData('store_' . $this->_getQuoteIdKey(), $storeId);
    }
    
    public function clear()
    {
    	parent::clear();
        $this->setStoreId(null);
    }

}

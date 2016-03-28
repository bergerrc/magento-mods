<?php

/**
 * Indexa Frete Facil
 *
 * @category   Shipping Method
 * @package    Indexa_Fretefacil
 * @author     Indexa Team <desenvolvimento@indexainternet.com.br>
 * @copyright  Copyright (c) 2011 Indexa
 */
class Indexa_Fretefacil_Model_Carrier_Indexafretefacil extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    /**
     * _code property
     *
     * @var string
     */
    protected $_code = 'indexafretefacil';

    /**
     * _result property
     *
     * @var Mage_Shipping_Model_Rate_Result | Mage_Shipping_Model_Tracking_Result
     */
    protected $_result = null;
    
    /**
     * $request property
     *
     * @var Mage_Shipping_Model_Rate_Request
     */
    protected $request;
    
    /**
     * $error property
     *
     * @var boolean
     */
    protected $error = false;

    /**
     * Test URL
     * 
     * @var string
     */
    const WS_TEST_MODE_ENABLED_URL = 'https://sistemas.homol.fastsolutions.com.br/FretesPayPalWS/WSFretesPayPal?wsdl';

    /**
     * Production URL
     * 
     * @var string
     */
    const WS_TEST_MODE_DISABLED_URL = 'https://ff.paypal-brasil.com.br/FretesPayPalWS/WSFretesPayPal?wsdl';

    /**
     * Location Test URL
     * 
     * @var string
     */
    const WS_LOCATION_TEST_URL = 'https://sistemas.homol.fastsolutions.com.br/FretesPayPalWS/WSFretesPayPal';

    /**
     * Location Production URL
     * 
     * @var string
     */
    const WS_LOCATION_PROD_URL = 'https://ff.paypal-brasil.com.br/FretesPayPalWS/WSFretesPayPal';

    /**
     * define weight unit
     * 
     * @var string
     */
    const WEIGHT_UNIT = 1;
    /**
     * shipping method code
     * 
     * @var string
     */
    const SHIPPING_METHOD_CODE = 'indexafretefacil_indexafretefacil';

    /**
     * @param boolean $error
     * 
     * return void
     */
    public function setError( $error ){
        $this->error = $error;
    }
    
    /**
     * @return boolean
     */
    public function hasError(){
        return  $this->error; 
    }
    
    /**
     * @param Mage_Shipping_Model_Rate_Request $request 
     * 
     * return void
     */
    public function setRequest( Mage_Shipping_Model_Rate_Request $request ){
        $this->request = $request;
    }
    
    /**
     * @return Mage_Shipping_Model_Rate_Request 
     */
    public function getRequest(){
        return  $this->request; 
    }
    
    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable() {
        return false;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods() {
        return array($this->_code => $this->getConfigData('title'));
    }

    /**
     * Collects the shipping rates from Frete facil.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {

        /**
         * check if this method is active
         */
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $this->setRequest($request);
        
        $result = Mage::getModel('shipping/rate_result');

        $error = Mage::getModel('shipping/rate_result_error')
                ->setCarrier($this->_code)
                ->setErrorMessage($this->getConfigData('error_shipping'))
                ->setCarrierTitle($this->getConfigData('title'));

        $shippingPrice = $this->getShippingPrice();
        
        /**
         * something is wrong when API returns less then zero
         */
        if ( $this->hasError() ) {
            return $error;
        }
        
        /**
         * set carrier values
         */
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier($this->_code)
                ->setCarrierTitle($this->getConfigData('title'))
                ->setCost($shippingPrice)
                ->setPrice($shippingPrice)
                ->setMethod($this->_code)
                ->setMethodTitle("SEDEX {$this->getConfigData('description')} ");

        $result->append($method);
        $this->_result = $result;

        $this->_updateFreeMethodQuote($request);

        return $this->_result;
    }

    /**
     * prepare product params
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * 
     * @return int|float 
     */
    public function calculate($item) {
        
        /**
         * get attributes
         */
        $cProduct = Mage::getModel('catalog/product')->load( $item->getProduct()->getId() );
        
        $measureA = $cProduct->getPaypalAltura();
        $measureB = $cProduct->getPaypalLargura();
        $measureC = $cProduct->getPaypalComprimento();
        
        $measureW = $cProduct->getWeight();
        
        /**
         * multiply quantity
         */
        if( $measureB < $measureC ){
            $measureB = $measureB * $item->getQty();
        }else{
            $measureC = $measureC * $item->getQty();
        }
        
        /**
         * keeping paypal_altura < paypal_comprimento
         */
        if( $measureA > $measureC ){
            $tmpMeasure = $measureC;
            $measureC = $measureA;
            $measureA = $tmpMeasure;
            $tmpMeasure = false;
        }
    
        $lMin = 11;
        $cMin = 16;
        $aMin = 2;
        /**
         * prepare params
         */
        $soapParams['peso'] = ($measureW * $item->getQty()) / self::WEIGHT_UNIT;
        $soapParams['altura'] = $measureA < $aMin ? $aMin: $measureA;
        $soapParams['largura'] = $measureB < $lMin ? $lMin: $measureB;
        $soapParams['profundidade'] = $measureC < $cMin ? $cMin: $measureC;
        
        return $this->call($soapParams);
    }

    /**
     * send request to paypal frete facil
     * 
     * @param array $soapParams
     * @return int|float 
     */
    public function call( $soapParams ){
        /**
         * set postal codes
         */
        $soapParams['cepOrigem'] = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());
        $soapParams['cepDestino'] = $this->getRequest()->getDestPostcode();

        /**
         * prepare soap url and location
         */
        if ($this->getConfigData('test_mode')) {
            $soapUrl = self::WS_TEST_MODE_ENABLED_URL;
            $soapLocation = self::WS_LOCATION_TEST_URL;
        } else {
            $soapUrl = self::WS_TEST_MODE_DISABLED_URL;
            $soapLocation = self::WS_LOCATION_PROD_URL;
        }

        /**
         * soap instance
         */
        $oPaypalFrete = new SoapClient($soapUrl, array("connection_timeout" => 120, "location" => $soapLocation));
        
        $result = $oPaypalFrete->getPreco($soapParams)->return;
        if( $result <= 0 ){
            $this->setError(true);
        }
        return $result;
    }
    /**
     * calculates total value from quote
     * 
     * @return int|float
     */
    public function getShippingPrice() {
        /**
         * get quote items
         */
        $items = Mage::getSingleton('checkout/cart')->getItems();
        
        $sumTotals = 0;
        if($items){
            foreach ($items as $item){
                /**
                 * calculate price by items
                 */
                $sumTotals += $this->calculate($item);
            }
        }
        
        return $sumTotals;
    }

}

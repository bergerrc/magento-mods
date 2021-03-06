<?php
require_once(dirname(__FILE__).'/frete.php');
//Cep inexistente 02154234
class PagSeguro_Model_Carrier_ShippingMethod
    extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_code = 'pagseguro';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        $valids = $this->getConfigData('aceita');
        if ($valids=='AMBOS') {
            $valids = array('PAC', 'Sedex');
        } else {
            $valids = array($valids);
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $peso    = $request->getPackageWeight();
        $destino = $request->getDestPostcode();
        $valor   = $request->getPackageValue();

        $frete = $this->pegaFrete($peso, $destino, $valor); // Peso, destino, valor

        if (in_array('Sedex', $valids)) {
            $method->setCarrier($this->_code);
            $method->setCarrierTitle('PagSeguro');

            $method->setMethod($this->_code.':Sedex');
            $method->setMethodTitle('Sedex');
            $method->setPrice($frete['Sedex']);

            $result->append($method);
        }
        // Setando valores para PAC
        if (in_array('PAC', $valids)) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier($this->_code);
            $method->setCarrierTitle('PagSeguro');

            $method->setMethod($this->_code.':PAC');
            $method->setMethodTitle('PAC');
            $method->setPrice($frete['PAC']);

            $result->append($method);
        }
        /*
        }else{
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle("PagSeguro");
            $error->setErrorMessage(utf8_encode($frete));
            return $error;
        }*/
        return $result;
    }

    public function isZipCodeRequired()
    {
        return true;
    }

    public function pegaFrete($peso, $destino, $valor = '0')
    {
        $origem = $this->getConfigData('origem');
        $origem = preg_replace('/\D/', '', $origem);
        $origem = substr($origem, 0, 5).'-'.substr($origem, 5);
        $peso   = (int) $peso;
        $frete  = new PgsFrete;
        return $frete->gerar($origem, $peso, $valor, $destino);
    }
}

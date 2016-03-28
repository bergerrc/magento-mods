<?php
/**
 * Magento PagamentoDigital Payment Modulo
 *
 * @category   Mage
 * @package    Mage_PagamentoDigital
 * @copyright  Author Guilherme Dutra (godutra@gmail.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PagamentoDigital Payment Action Dropdown source
 *
 */
class Mage_Pagamentodigital_Model_Source_StandardAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => Mage_Pagamentodigital_Model_Standard::PAYMENT_TYPE_AUTH, 'label' => Mage::helper('Pagamentodigital')->__('Authorization')),
            array('value' => Mage_Pagamentodigital_Model_Standard::PAYMENT_TYPE_SALE, 'label' => Mage::helper('Pagamentodigital')->__('Sale')),
        );
    }
}
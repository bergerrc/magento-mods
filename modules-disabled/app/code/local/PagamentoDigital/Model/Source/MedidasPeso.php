<?php
/**
 * Magento Pagamento Digital Payment Modulo
 *
 * @category   Shipping
 * @package    Pagamento Digital
 * @copyright  Author Guilherme Dutra (godutra@gmail.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PagamentoDigital Payment Action Dropdown source
 *
 */
class PagamentoDigital_Model_Source_MedidasPeso
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'gr', 'label' => 'gramas'),
            array('value' => 'kg', 'label' => 'kilogramas'),
        );
    }
}

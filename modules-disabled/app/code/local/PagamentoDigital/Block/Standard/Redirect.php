<?php

class PagamentoDigital_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $standard = Mage::getModel('pagamentodigital/standard');

        $form = new Varien_Data_Form();
        $form->setAction($standard->getPagamentoDigitalUrl())
            ->setId('pagamentodigital_standard_checkout')
            ->setName('pagamentodigital_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($standard->getStandardCheckoutFormFields() as $field=>$value) {
            $form->addField($field, 'text', array('name'=>$field, 'value'=>$value));
        }
        $form->addField('button', 'submit', array('name'=>'Submit', 'value'=>'Enviar'));
        $html  = '<html>';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
		$html .= '<body>';
        $html .= $this->__('Você será redirecionado para o Pagamento Digital em alguns instantes.');
        $html .= $form->toHtml();
        $html .= '<script type="text/javascript">//document.getElementById("pagamentodigital_standard_checkout").submit();</script>';
        $html .= '</body></html>';

        return $html;
    }
}
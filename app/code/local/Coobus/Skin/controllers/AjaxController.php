<?php

/**
 * 
 * Controle responsavel por receber um cep e devolver o endereco encontrado.
 * 
 * - republica: http://www.republicavirtual.com.br/cep/exemplos.php
 * - kinghost: http://manual.kinghost.net/web.html
 * - locaweb: http://wiki.locaweb.com.br/pt-br/Correios_-_localiza_logradouro
 * 
 * @category    m—dulo
 * @package     local_MarioSAM
 * @copyright   MarioSAM (http://www.mariosam.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @author mariosam
 * 
 */
class Coobus_Skin_AjaxController extends Mage_Core_Controller_Front_Action {
    
	protected $_baseurlConfig;
	
	/**
	 * Metodo executado pela url via ajax para recuperar informacoes do cep do cliente.
	 * 
	 */
	public function addressAction() {
		$this->_baseurlConfig = Mage::getStoreConfig('shipping/autocomplete/base_url');
		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_URL, $this->_idUrl() );
		$data = $this->_tratamentoRetorno( curl_exec( $ch ) );
		
		curl_close( $ch );
		echo $data;
    }

    /**
     * Identifica qual base Url deve ser feita a consulta via webservice.
     * 
     */
    protected function _idUrl() {
		switch( $this->_baseurlConfig ) {
			case 1:
				return 'http://republicavirtual.com.br/web_cep.php?formato=json&cep='.urlencode( $this->getRequest()->getParam('cep', false) );
				//formato retorno
				//{"resultado":"1","resultado_txt":"sucesso - cep completo","uf":"SC","cidade":"Florian\u00f3polis","bairro":"Estreito","tipo_logradouro":"Rua","logradouro":"Afonso Pena"} 
				break;
			case 2:
				return 'http://webservice.kinghost.net/web_cep.php?formato=javascript&auth=b14a7b8059d9c055954c92674ce60032&cep='.urlencode( $this->getRequest()->getParam('cep', false) );
				//formato retorno
				//var resultadoCEP = { 'uf' : 'SC', 'cidade' : 'Florian%F3polis', 'bairro' : 'Estreito', 'tipo_logradouro' : 'Rua', 'logradouro' : 'Afonso%20Pena', 'resultado' : '1', 'resultado_txt' : 'sucesso%20-%20cep%20completo' } 
				break;
			case 3:
				return 'locaweb: nao funcionando';
				break;
		}
    }
    
    protected function _tratamentoRetorno( $dados='' ) {
    	switch( $this->_baseurlConfig ) {
			case 1:
				return $dados; 
				break;
			case 2:
				return urldecode( substr($dados, strpos($dados, '{')) ); 
				break;
			case 3:
				return 'locaweb: nao funcionando';
				break;
		}
    }

}


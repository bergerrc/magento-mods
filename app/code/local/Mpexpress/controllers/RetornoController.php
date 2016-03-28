<?php
/**
 * Retorno autom�tico do MercadoPago
 *
 * Faz a requisi��o e verifica��o do POST recebido pelo MercadoPago
 *
 * PHP Version 5
 *
 * @category  MercadoPago
 * @package   MercadoPago
 */

if (!defined('TOKEN')) {
	define('TOKEN', '');
}

/**
 * Mpexpress_RetornoController
 *
 * Classe de manipula��o para o retorno do post do mercadopago
 *
 * @category MercadoPago
 * @package  MercadoPago
 * @copyright  Copyright (c) 2010 MercadoPago [https://www.mercadopago.com/mp-brasil/]  - Fulvio Cunha [fulvio.cunha@mercadolivre.com]
 */

class Mpexpress_RetornoController extends Mage_Core_Controller_Front_Action
{
	/**
	 * _preparaDados
	 *
	 * Prepara os dados vindos do post e converte-os para url, adicionando
	 * o token do usuario quando necessario.
	 *
	 * @param array $post        Array contendo os posts do mercadopago
	 * @param bool  $confirmacao Controlando a adicao do token no post
	 *
	 * @return string
	 *
	 * @access private
	 *
	 * @internal � usado pela {@see Mpexpress_RetornoController::verifica} para gerar os,
	 * dados que ser�o enviados pelo MercadoPago
	 */
	private function _preparaDados($post, $confirmacao=true)
	{
		if ('array' !== gettype($post)) {
			$post = array();
		}
		if ($confirmacao) {
			$post['Comando'] = 'validar';
			$post['Token']   = TOKEN;
		}
		$retorno = array();
		foreach ($post as $key => $value) {
			if ('string' !== gettype($value)) {
				$post[$key] = '';
			}
			$value     = urlencode(stripslashes($value));
			$retorno[] = "{$key}={$value}";
		}
		return implode('&', $retorno);
	}


	private function _tipoEnvio()
	{
		// Prefira utilizar a fun��o CURL do PHP
		// Leia mais sobre CURL em: http://us3.php.net/curl
		global $_retMercadoPagoErrNo, $_retMercadoPagoErrStr;
		if (function_exists('curl_exec')) {
			return array(
				'curl', 
				'https://www.mercadopago.com/mlb/buybutton'
				);
		} elseif ((PHP_VERSION >= 4.3) && 
			  ($fp = @fsockopen('ssl://www.mercadopago.com',
			  443, $_retMercadoPagoErrNo, $_retMercadoPagoErrStr, 30))
		) {
			return array('fsocket', '/mlb/buybutton', $fp);
		} elseif ($fp = @fsockopen('www.mercadopago.com', 80,
			$_retMercadoPagoErrNo, $_retMercadoPagoErrStr, 30)
		) {
			return array('fsocket', '/mlb/buybutton', $fp);
		}
		return array ('', '');
	}

	/**
	 * _confirma
	 *
	 * Faz a parte Server-Side, verificando os dados junto ao MercadoPago
	 *
	 * @param array $tipoEnvio Array com a configura��o gerada
	 *                         por {@see Retorno::_tipoEnvio()}
	 * @param array $post      Dados vindos no POST do MercadoPago para serem
	 *                         verificados
	 *
	 * @return bool
	 * @global string $_retMercadoPagoErrNo   Numero de erro do mercadopago
	 * @global string $_retMercadoPagoErrStr  Texto descritivo do erro do mercadopago
	 */
	private function _confirma($tipoEnvio, $post) 
	{
		global $_retMercadoPagoErrNo, $_retMercadoPagoErrStr;
		$spost    = $this->_preparaDados($post);
		$confirma = false;
		// Usando a biblioteca cURL
		if ($tipoEnvio[0] === 'curl') {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $tipoEnvio[1]);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $spost);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// Deve funcionar apenas em teste
			if (defined('MERCADOPAGO_AMBIENTE_DE_TESTE')) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			}
			$resp = curl_exec($ch);
			if (!$this->notNull($resp)) {
				curl_setopt($ch, CURLOPT_URL, $tipoEnvio[1]);
				$resp = curl_exec($ch);
			}
			curl_close($ch);
			$confirma = (strcmp($resp, 'VERIFICADO') == 0);

			// Usando fsocket
		} elseif ($tipoEnvio[0] === 'fsocket') {
			if (!$tipoEnvio[2]) {
				die ("{$_retMercadoPagoErrStr} ($_retMercadoPagoErrNo)");
			} else {
				$cabecalho  = "POST {$tipoEnvio[1]} HTTP/1.0\r\n";
				$cabecalho .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$cabecalho .= "Content-Length: " . strlen($spost) . "\r\n\r\n";
				$resp       = '';
				fwrite($tipoEnvio[2], "{$cabecalho}{$spost}");
				while (!feof($tipoEnvio[2])) {
					$resp = fgets($tipoEnvio[2], 1024);
					if (strcmp($resp, 'VERIFICADO') == 0) {
						$confirma = true;
						break;
					}
				}
				fclose($tipoEnvio[2]);
			}
		}
		return $confirma;
	}

	/**
	 * notNull
	 *
	 * Extraido de OScommerce 2.2 com base no original do mercadopago,
	 * Checa se o valor e nulo
	 *
	 * @param mixed $value Vari�vel a ser checada se � nula
	 *
	 * @return bool
	 * @access static
	 */
	private function notNull($value)
	{
		if (is_array($value)) {
			if (sizeof($value) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			if (($value != '') && (strtolower($value) != 'null') &&
				(strlen(trim($value)) > 0)
			) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * verifica
	 *
	 * Verifica o tipo de conex�o aberta e envia os dados vindos
	 * do post
	 *
	 * @param array $post      Array contendo os posts do mercadopago
	 * @param bool  $tipoEnvio (opcional) Verifica o tipo de envio do post
	 *
	 * @access static
	 * @use Mpexpress_RetornoController::_tipoenvio()
	 * @use Mpexpress_RetornoController::_confirma()
	 * @return bool
	 */

	public function indexAction($tipoEnvio=false, $function = null)
	{
		$post = $this->getRequest()->getParams();

		if ('array' !== gettype($tipoEnvio)) {
			$tipoEnvio = $this->_tipoEnvio();
		}
		if (!in_array($tipoEnvio[0], array('curl', 'fsocket'))) {
			return false;
		}
		$confirma = $this->_confirma($tipoEnvio, $post);

		if ($confirma) {
			$itens = array (
					'VendedorEmail', 'TransacaoID', 'Referencia', 'TipoFrete',
					'ValorFrete', 'Anotacao', 'DataTransacao', 'TipoPagamento',
					'StatusTransacao', 'CliNome', 'CliEmail', 'CliEndereco',
					'CliNumero', 'CliComplemento', 'CliBairro', 'CliCidade',
					'CliEstado', 'CliCEP', 'CliTelefone', 'NumItens',
					);
			foreach ($itens as $item) {
				if (!isset($post[$item])) {
					$post[$item] = '';
				}
				if ($item=='ValorFrete') {
					$post[$item] = str_replace(',', '.', $post[$item]);
				}
			}
			$produtos = array ();
			$total = 0;
			for ($i=1;isset($post["ProdID_{$i}"]);$i++) {
				$produto = self::makeProd($post, $i);
				$produtos[] = $produto;
				unset($produto['ProdID'], $produto['ProdDescricao']);
				// Hack apenas para o M�dulo Magento
				$total += $produto['ProdValor'] * $produto['ProdQuantidade'];
			}
			$total += self::convertNumber($post['ValorFrete']);
			if (function_exists('retorno_automatico') AND !$function) {
				$function = 'retorno_automatico';
			}
			if ($function) {
				call_user_func($function, $post['Referencia'],
					$post['StatusTransacao'], $total,
					$produtos, (object) $post);
			}
		}
		return $confirma;
	}

	/**
	 * Gera o produto baseado no post e no id enviados
	 *
	 * @param array $post O post enviado pelo MercadoPago
	 * @param int   $i    ID do produto que deseja gerar
	 *
	 * @return array
	 */
	private function makeProd ($post, $i)
	{
		$post += array ("ProdFrete_{$i}"=>0, "ProdExtras_{$i}" => 0);
		return array (
				'ProdID'          => $post["ProdID_{$i}"],
				'ProdDescricao'   => $post["ProdDescricao_{$i}"],
				'ProdValor'       => self::convertNumber($post["ProdValor_{$i}"]),
				'ProdQuantidade'  => $post["ProdQuantidade_{$i}"],
				'ProdFrete'       => self::convertNumber($post["ProdFrete_{$i}"]),
				'ProdExtras'      => self::convertNumber($post["ProdExtras_{$i}"]),
				);
	}

	/**
	 * Converte o numero enviado para padr�o numerico
	 *
	 * @param string|int|double $number Numero que deseja converter
	 * 
	 * @return double
	 */

	private function convertNumber ($number)
	{
		$number = preg_replace('/\D/', '', $number) / 100;
		return (double) (str_replace(',', '.', $number));
	}

}

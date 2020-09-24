<?php
defined('_JEXEC') or die('Restricted access');
include("PG_Signature.php");

class pm_paybox extends PaymentRoot
{

	function showPaymentForm($params, $pmconfigs)
	{
		include(dirname(__FILE__)."/paymentform.php");
	}

	function showAdminFormParams($params)
	{
		$jmlThisDocument = & JFactory::getDocument();
		switch ($jmlThisDocument->language)
		{
			case 'en-gb': include(JPATH_SITE.'/administrator/components/com_jshopping/lang/en-GB_paybox.php'); $language = 'en'; break;
			case 'ru-ru': include(JPATH_SITE.'/administrator/components/com_jshopping/lang/ru-RU_paybox.php'); $language = 'ru'; break;
			default: include(JPATH_SITE.'/administrator/components/com_jshopping/lang/ru-RU_paybox.php');
		}
		$array_params = array('test_mode', 'merchant_id', 'secret_key', 'transaction_end_status', 'transaction_pending_status', 'transaction_failed_status');
		foreach ($array_params as $key)
			if (!isset($params[$key]))
				$params[$key] = '';
		$orders = &JModelLegacy::getInstance('orders', 'JshoppingModel');
		$currency = &JModelLegacy::getInstance('currencies', 'JshoppingModel');

		include(dirname(__FILE__)."/adminparamsform.php");

		JHtml::_('tabs.end');
	}

	function checkTransaction($pmconfigs, $order, $act)
	{
		switch ($act) {
			case 'check':
				unset($_GET['Itemid']);
				$arrParams = $_GET;
				$thisScriptName = PG_Signature::getOurScriptName();

				if ( !PG_Signature::check($arrParams['pg_sig'], $thisScriptName, $arrParams, $pmconfigs['secret_key']) )
					die("Bad signature");

				$order_id = $arrParams['pg_order_id'];
				/*
				 * Проверка того, что заказ ожидает оплаты
				 */
				if($pmconfigs['transaction_pending_status'] == $order->order_status)
					$is_order_available = true;
				else{
					$is_order_available = false;
					$error_desc = "Товар не доступен";
				}

				$arrResp['pg_salt']              = $arrParams['pg_salt']; // в ответе необходимо указывать тот же pg_salt, что и в запросе
				$arrResp['pg_status']            = $is_order_available ? 'ok' : 'error';
				$arrResp['pg_error_description'] = $is_order_available ?  ""  : $error_desc;
				$arrResp['pg_sig'] = PG_Signature::make($thisScriptName, $arrResp, $pmconfigs['secret_key']);

				$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
				$xml->addChild('pg_salt', $arrResp['pg_salt']); // в ответе необходимо указывать тот же pg_salt, что и в запросе
				$xml->addChild('pg_status', $arrResp['pg_status']);
				$xml->addChild('pg_error_description', htmlentities($arrResp['pg_error_description']));
				$xml->addChild('pg_sig', $arrResp['pg_sig']);
				echo $xml->asXML();
				die();
				break;


			case 'result':
				unset($_GET['Itemid']);
				$checkout = JModelLegacy::getInstance('checkout', 'jshop');
				$arrParams = $_GET;
				$thisScriptName = PG_Signature::getOurScriptName();
				if ( !PG_Signature::check($arrParams['pg_sig'], $thisScriptName, $arrParams, $pmconfigs['secret_key']) )
					die("Bad signature");

				$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
				$order_id = $arrParams['pg_order_id'];
				$js_result_status = 'ok';
				$pg_description = 'Оплата принята';

				if ( $arrParams['pg_result'] == 1 ) {
					if($pmconfigs['transaction_pending_status'] == $order->order_status) {
						$checkout->changeStatusOrder($order->order_id, $pmconfigs['transaction_end_status'], 1);
					}
					else {
						$js_result_status = 'error';
						$pg_description = 'Оплата не может быть принята';
						$xml->addChild('pg_error_description', 'Оплата не может быть принята');
						if($arrParams['pg_can_reject']){
							$js_result_status = 'reject';
						}
					}
				}
				else {
					$checkout->changeStatusOrder($order->order_id, $pmconfigs['transaction_failed_status'], 1);
				}
				// обрабатываем случай успешной оплаты заказа с номером $order_id
				$xml->addChild('pg_salt', $arrParams['pg_salt']); // в ответе необходимо указывать тот же pg_salt, что и в запросе
				$xml->addChild('pg_status', $js_result_status);
				$xml->addChild('pg_description', $pg_description);
				$xml->addChild('pg_sig', PG_Signature::makeXML($thisScriptName, $xml, $pmconfigs['secret_key']));
				print $xml->asXML();
				die();
				break;


			default:
				break;
		}
	}

	function showEndForm($pmconfigs, $order)
	{
		$check_url = JURI::root() . "index.php?option=com_jshopping&controller=checkout&task=step7&act=check&js_paymentclass=pm_paybox&type=check&order_id=".$order->order_id;
		$result_url = JURI::root() . "index.php?option=com_jshopping&controller=checkout&task=step7&act=result&js_paymentclass=pm_paybox&type=check&order_id=".$order->order_id;

		$success_url = JURI::root(). 'index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_paybox&order_id='.$order->order_id;
		$failure_url = JURI::root(). 'index.php?option=com_jshopping&controller=checkout&task=step7&act=cancel&js_paymentclass=pm_paybox&order_id='.$order->order_id;

		// sum of order
		$out_summ = $order->order_total / $order->currency_exchange;

		$arrReq = array();
		/* Обязательные параметры */
		$arrReq['pg_merchant_id'] = $pmconfigs['merchant_id'];// Идентификатор магазина
		$arrReq['pg_order_id']    = $order->order_id;		// Идентификатор заказа в системе магазина
		$arrReq['pg_amount']      = sprintf("%01.2f",$out_summ);		// Сумма заказа
		$arrReq['pg_description'] = "Оплата заказа ".$_SERVER['HTTP_HOST']; // Описание заказа (показывается в Платёжной системе)
		$arrReq['pg_user_ip'] = $_SERVER['REMOTE_ADDR']; // Описание заказа (показывается в Платёжной системе)
		$arrReq['pg_site_url'] = $_SERVER['HTTP_HOST']; // Для возврата на сайт
		$arrReq['pg_lifetime'] = $pmconfigs['lifetime']*60*60; // Время жизни в секундах

		$arrReq['pg_check_url'] = $check_url; // Проверка заказа
		$arrReq['pg_result_url'] = $result_url; // Оповещение о результатах
		$arrReq['pg_success_url'] = $success_url; // В случае успешной оплаты
		$arrReq['pg_failure_url'] = $failure_url; // В случае отмены оплаты

		if(isset($order->d_phone)){ // Телефон в 11 значном формате
			$strUserPhone = preg_replace('/\D+/','',$order->d_phone);
			if(strlen($strUserPhone) == 10)
				$strUserPhone .= "7";
			$arrReq['pg_user_phone'] = $strUserPhone;
		}

		if(isset($order->d_email)){
			$arrReq['pg_user_contact_email'] = $order->d_email;
			$arrReq['pg_user_email'] = $order->d_email; // Для ПС Деньги@Mail.ru
		}

		$jmlThisDocument = & JFactory::getDocument();
		switch ($jmlThisDocument->language)
		{
			case 'en-gb': $language = 'EN'; break;
			case 'ru-ru': $language = 'RU'; break;
			default: $language = 'EN'; break;
		}

		$arrReq['pg_language'] = $language;
		$arrReq['pg_testing_mode'] = $pmconfigs['test_mode']?1:0;

		if($order->currency_code_iso == "RUB")
			$arrReq['pg_currency'] = "RUR";
		else
			$arrReq['pg_currency'] = $order->currency_code_iso;

		$arrReq['pg_salt'] = rand(21,43433);
		$arrReq['pg_sig'] = PG_Signature::make('payment.php', $arrReq, $pmconfigs['secret_key']);
		$query = http_build_query($arrReq);

		$order->order_status = $pmconfigs["transaction_pending_status"];

		header("Location: https://api.paybox.money/payment.php?$query");
	}

	function getUrlParams($pmconfigs)
	{
		$params = array();
		$params['order_id'] = JRequest::getInt("order_id");
		return $params;
	}

}
?>

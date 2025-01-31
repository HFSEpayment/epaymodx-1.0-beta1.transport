<?php

$modelpath = $modx->getOption('core_path') . 'components/shopkeeper3/model/';
$modx->addPackage( 'shopkeeper3', $modelpath );

if (isset($_REQUEST['payment']) && $_REQUEST['payment'] == 'hbepay') {
	$payment_form = $modx->getOption('Payment_form', $scriptProperties, null);
	$modx->sendRedirect($payment_form);
}

if (isset($scriptProperties['action'])) {

	if (isset($_SESSION['shk_order_id'])) {
		$order_id = $_SESSION['shk_order_id'];
	}
	else {
		$order_id = $_SESSION['shk_lastOrder']['id'];
	}

	$order = $modx->getObject('shk_order', $order_id);

	switch ($scriptProperties['action']) {
		case 'fail':

			$order->set('status', 5);
			$order->save();

			return '';
			break;
		case 'success':

			$order->set('status', 6);
			$order->save();
			return '';
			break;
		case 'callback':
			break;
		case 'payment':

			$client_id = $modx->getOption('Client_id',$scriptProperties,null);
			$client_secret = $modx->getOption('Client_secret',$scriptProperties,null);
			$terminal = $modx->getOption('Terminal',$scriptProperties,null);
			$env = $modx->getOption('Test',$scriptProperties,null);
			$backlink = $modx->getOption('Backlink',$scriptProperties,null);
			$failure_backlink = $modx->getOption('Failure_backlink',$scriptProperties,null);
			$currency = $modx->getOption('Currency',$scriptProperties,null);

			if (isset($_SESSION['shk_order_id'])) {
				$amount = number_format($_SESSION['shk_order_price'], 2, '.', '');
			}
			else {
				$amount = number_format($_SESSION['shk_lastOrder']['price'], 2, '.', '');
			}

			if (!$order_id) {
				return "Заказ не найден.";
			}

			if (!$order) {
				die('no shk_order object found');
			}

			$order->set('status', 1);
			$order->save();

			?>
			<form action='' method='post'>
			<input type='submit' name='submit' value='Оплатить сейчас'>
			</form>
<?php

	function gateway($client_id, $client_secret, $terminal, $env, $backlink, $failure_backlink, $order_id, $amount, $currency){

		$test_url = "https://testoauth.homebank.kz/epay2/oauth2/token";
		$prod_url = "https://epay-oauth.homebank.kz/oauth2/token";
		$test_page = "https://test-epay.homebank.kz/payform/payment-api.js";
		$prod_page = "https://epay.homebank.kz/payform/payment-api.js";
		$hbp_env = $env;

		$token_api_url = "";
		$pay_page = "";
		$err_exist = false;
		$err = "";

		// initiate default variables
		$hbp_account_id = "";
		$hbp_telephone = "";
		$hbp_email = "";
		$hbp_currency = $currency;
		$hbp_description = "Оплата в интернет магазине";

		$hbp_client_id = $client_id;
		$hbp_client_secret = $client_secret;
		$hbp_terminal = $terminal;
		$hbp_invoice_id = '0000000'.$order_id;
		$hbp_amount = $amount;

		$hbp_back_link = $backlink;
		$hbp_failure_back_link = $failure_backlink;
		$hbp_post_link = "";
		$hbp_failure_post_link = "";
		

		if ($hbp_env) {
				$token_api_url = $test_url;
				$pay_page = $test_page;
		} else {
				$token_api_url = $prod_url;
				$pay_page = $prod_page;
		}
		
		$fields = [
				'grant_type'      => 'client_credentials', 
				'scope'           => 'payment usermanagement',
				'client_id'       => $hbp_client_id,
				'client_secret'   => $hbp_client_secret,
				'invoiceID'       => $hbp_invoice_id,
				'amount'          => $hbp_amount,
				'currency'        => $hbp_currency,
				'terminal'        => $hbp_terminal,
				'postLink'        => $hbp_post_link,
				'failurePostLink' => $hbp_failure_post_link
			];
		
			$fields_string = http_build_query($fields);
		
			$ch = curl_init();
		
			curl_setopt($ch, CURLOPT_URL, $token_api_url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		
			$result = curl_exec($ch);
		
			$json_result = json_decode($result, true);
		if (!curl_errno($ch)) {
			switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
				case 200:
					$hbp_auth = (object) $json_result;
		
					$hbp_payment_object = (object) [
						"invoiceId" => $hbp_invoice_id,
						"backLink" => $hbp_back_link,
						"failureBackLink" => $hbp_failure_back_link,
						"postLink" => $hbp_post_link,
						"failurePostLink" => $hbp_failure_post_link,
						"language" => $hbp_language,
						"description" => $hbp_description,
						"accountId" => $hbp_account_id,
						"terminal" => $hbp_terminal,
						"amount" => $hbp_amount,
						"currency" => $hbp_currency,
						"auth" => $hbp_auth,
						"phone" => $hbp_telephone,
						"email" => $hbp_email
					];
				?>
				<script src="<?=$pay_page?>"></script>
				<script>
					halyk.pay(<?= json_encode($hbp_payment_object) ?>);
				</script>
			<?php
					break;
				default:
					echo 'Неожиданный код HTTP: ', $http_code, "\n";
			}
		}
	}
		if(isset($_POST['submit']))
	{
  			gateway($client_id, $client_secret, $terminal, $env, $backlink, $failure_backlink, $order_id, $amount, $currency);
	}
	
	return '';
			break;
		default:
			break;
	}
}
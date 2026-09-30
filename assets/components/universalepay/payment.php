<?php
define('MODX_API_MODE', true);
$core = dirname(__DIR__, 3) . '/core/';
define('MODX_CORE_PATH', $core);
define('MODX_CONFIG_KEY', 'config');
require_once $core . 'config/config.inc.php';
require_once $core . 'vendor/autoload.php';

$modx = new \MODX\Revolution\modX();
$modx->initialize('web');

$orderId = (string)($_GET['order'] ?? '');
$stored = $_SESSION['universalepay_payment'][$orderId] ?? null;

if (!$orderId || !is_array($stored) || empty($stored['payment'])) {
    http_response_code(400);
    exit('Payment session not found');
}

if (!empty($stored['created']) && (time() - (int)$stored['created']) > 1800) {
    unset($_SESSION['universalepay_payment'][$orderId]);
    http_response_code(410);
    exit('Payment session expired');
}

$payload = $stored['payment'];
$test = !empty($stored['test']);
$js = $test
    ? 'https://test-epay.homebank.kz/payform/payment-api.js'
    : 'https://epay.homebank.kz/payform/payment-api.js';

header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Оплата заказа</title>
</head>
<body>
<p>Открываем форму оплаты Halyk ePay…</p>
<script src="<?=htmlspecialchars($js, ENT_QUOTES, 'UTF-8')?>"></script>
<script>
const payment = <?=json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)?>;
if (typeof halyk === 'undefined' || typeof halyk.pay !== 'function') {
    document.body.innerHTML = '<p>Не удалось загрузить платёжную форму Halyk ePay.</p>';
} else {
    halyk.pay(payment);
}
</script>
</body>
</html>

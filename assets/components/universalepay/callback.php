<?php
define('MODX_API_MODE', true);
$core = dirname(__DIR__, 3) . '/core/';
define('MODX_CORE_PATH', $core);
define('MODX_CONFIG_KEY', 'config');

require_once $core . 'config/config.inc.php';
require_once $core . 'vendor/autoload.php';

$modx = new \MODX\Revolution\modX();
$modx->initialize('web');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$invoice = (string)($data['invoiceId'] ?? $data['invoiceID'] ?? '');
$invoiceDigits = preg_replace('/\D+/', '', $invoice) ?: '';
$orderId = (int)ltrim($invoiceDigits, '0');

$modx->log(
    \MODX\Revolution\modX::LOG_LEVEL_INFO,
    '[UniversalEpay] Halyk callback invoice=' . $invoice . ' data=' .
    json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

if ($orderId <= 0) {
    http_response_code(400);
    exit('invalid invoiceID');
}

$order = $modx->getObject(\MiniShop3\Model\msOrder::class, $orderId);
if (!$order) {
    http_response_code(404);
    exit('order not found');
}

$test = (bool)$modx->getOption('universalepay.test_mode', null, true);
$prefix = $test ? 'test' : 'prod';
$clientId = trim((string)$modx->getOption("universalepay.{$prefix}_client_id", null, ''));
$clientSecret = trim((string)$modx->getOption("universalepay.{$prefix}_client_secret", null, ''));
$terminal = trim((string)$modx->getOption("universalepay.{$prefix}_terminal", null, ''));
$currency = strtoupper((string)$modx->getOption('universalepay.currency', null, 'KZT'));

if (!$clientId || !$clientSecret || !$terminal) {
    http_response_code(500);
    exit('UniversalEpay credentials are not configured');
}

$amount = (float)$order->get('cost');
if ($amount <= 0) {
    $amount = (float)$order->get('cart_cost') + (float)$order->get('delivery_cost') - (float)$order->get('discount_cost');
}
$amount = round(max(0, $amount), 2);

try {
    $client = new \UniversalEpay\Halyk\HalykClient(
        $clientId,
        $clientSecret,
        $terminal,
        $test,
        $currency,
        strtolower((string)$modx->getOption('universalepay.language', null, 'rus'))
    );

    $status = $client->checkStatus($client->invoiceId((string)$orderId));

    if ($client->isSuccessfulStatus($status, $amount, $currency)) {
        $paidStatus = (int)$modx->getOption('universalepay.paid_status', null, 3);
        if ((int)$order->get('status') !== $paidStatus) {
            $order->set('status', $paidStatus);
            $order->save();
        }
        $result = ['success' => true, 'verified' => true];
    } else {
        $result = ['success' => false, 'verified' => true];
    }
} catch (\Throwable $e) {
    $modx->log(
        \MODX\Revolution\modX::LOG_LEVEL_ERROR,
        '[UniversalEpay] callback verification failed: ' . $e->getMessage()
    );
    http_response_code(500);
    echo 'verification failed';
    exit;
}

http_response_code(200);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE);

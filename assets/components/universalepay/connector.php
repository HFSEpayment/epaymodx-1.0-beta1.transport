<?php
define('MODX_API_MODE', true);
$core = dirname(__DIR__, 3) . '/core/';
define('MODX_CORE_PATH', $core);
define('MODX_CONFIG_KEY', 'config');
require_once $core . 'config/config.inc.php';
require_once $core . 'vendor/autoload.php';

$modx = new \MODX\Revolution\modX();
if (!$modx->initialize('mgr') || !$modx->user || !$modx->user->isAuthenticated('mgr')) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Access denied']));
}

header('Content-Type: application/json; charset=UTF-8');

$action = (string)($_REQUEST['action'] ?? '');
$keys = [
    'test_mode' => 'universalepay.test_mode',
    'test_client_id' => 'universalepay.test_client_id',
    'test_client_secret' => 'universalepay.test_client_secret',
    'test_terminal' => 'universalepay.test_terminal',
    'prod_client_id' => 'universalepay.prod_client_id',
    'prod_client_secret' => 'universalepay.prod_client_secret',
    'prod_terminal' => 'universalepay.prod_terminal',
    'currency' => 'universalepay.currency',
    'language' => 'universalepay.language',
    'paid_status' => 'universalepay.paid_status',
    'failed_status' => 'universalepay.failed_status',
];

if ($action === 'get') {
    $out = [];
    foreach ($keys as $name => $key) {
        $out[$name] = $modx->getOption($key, null, '');
    }
    echo json_encode(['success' => true, 'object' => $out], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'save') {
    foreach ($keys as $name => $key) {
        if (!array_key_exists($name, $_POST)) {
            continue;
        }
        $value = $_POST[$name];
        if (in_array($name, ['test_mode','paid_status','failed_status'], true)) {
            $value = (string)(int)$value;
        }
        $setting = $modx->getObject(\MODX\Revolution\modSystemSetting::class, ['key' => $key]);
        if (!$setting) {
            $setting = $modx->newObject(\MODX\Revolution\modSystemSetting::class);
            $setting->set('key', $key);
            $setting->set('namespace', 'universalepay');
            $setting->set('area', 'universalepay');
        }
        $setting->set('value', (string)$value);
        $setting->save();
    }
    $modx->cacheManager->refresh(['system_settings' => []]);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action'], JSON_UNESCAPED_UNICODE);

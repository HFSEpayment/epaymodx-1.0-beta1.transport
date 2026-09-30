<?php
if (!defined('MODX_CORE_PATH')) {
    return;
}
$base = MODX_CORE_PATH . 'components/universalepay/src/';
spl_autoload_register(function ($class) use ($base) {
    $prefix = 'UniversalEpay\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $base . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) require_once $file;
});

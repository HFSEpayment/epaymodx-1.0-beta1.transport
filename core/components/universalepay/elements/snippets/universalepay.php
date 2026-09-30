<?php
/**
 * [[!universalepay? &action=`return`]]
 * Simple return handler for the browser.
 */
$action = $scriptProperties['action'] ?? 'return';
$orderId = (int)($_GET['order'] ?? 0);
$status = $_GET['status'] ?? '';

if ($action === 'return') {
    if ($status === 'success') return 'Оплата успешно завершена.';
    if ($status === 'fail') return 'Оплата не завершена.';
}
return '';

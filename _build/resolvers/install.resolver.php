<?php
if (!isset($object->xpdo)) {
    return true;
}
$modx = $object->xpdo;
$action = $options[\xPDO\Transport\xPDOTransport::PACKAGE_ACTION] ?? null;

if ($action === \xPDO\Transport\xPDOTransport::ACTION_INSTALL || $action === \xPDO\Transport\xPDOTransport::ACTION_UPGRADE) {
    $defaults = [
        ['universalepay.test_mode', '1', 'boolean', 'universalepay'],
        ['universalepay.test_client_id', '', 'textfield', 'universalepay'],
        ['universalepay.test_client_secret', '', 'password', 'universalepay'],
        ['universalepay.test_terminal', '', 'textfield', 'universalepay'],
        ['universalepay.prod_client_id', '', 'textfield', 'universalepay'],
        ['universalepay.prod_client_secret', '', 'password', 'universalepay'],
        ['universalepay.prod_terminal', '', 'textfield', 'universalepay'],
        ['universalepay.currency', 'KZT', 'textfield', 'universalepay'],
        ['universalepay.language', 'rus', 'textfield', 'universalepay'],
        ['universalepay.paid_status', '3', 'number', 'universalepay'],
        ['universalepay.failed_status', '4', 'number', 'universalepay'],
    ];

    foreach ($defaults as [$key, $value, $xtype, $area]) {
        $setting = $modx->getObject(\MODX\Revolution\modSystemSetting::class, ['key' => $key]);
        if (!$setting) {
            $setting = $modx->newObject(\MODX\Revolution\modSystemSetting::class);
            $setting->set('key', $key);
            $setting->set('namespace', 'universalepay');
            $setting->set('area', $area);
            $setting->set('xtype', $xtype);
            $setting->set('value', $value);
            $setting->save();
        }
    }

    // Add the UniversalEpay manager menu.
    $menu = $modx->getObject(\MODX\Revolution\modMenu::class, [
        'text' => 'universalepay',
        'namespace' => 'universalepay'
    ]);
    if (!$menu) {
        $menu = $modx->newObject(\MODX\Revolution\modMenu::class);
        $menu->fromArray([
            'text' => 'universalepay',
            'parent' => 'components',
            'description' => 'UniversalEpay — Halyk ePay',
            'menuindex' => 0,
            'params' => '',
            'handler' => '',
            'action' => 'home',
            'namespace' => 'universalepay',
        ], '', true, true);
        $menu->save();
    }

    // Automatically create a MiniShop3 payment method when MiniShop3 exists.
    $ms3Bootstrap = $modx->getOption('core_path') . 'components/minishop3/bootstrap.php';
    if (is_file($ms3Bootstrap)) {
        require_once $ms3Bootstrap;
    }
    if (class_exists(\MiniShop3\Model\msPayment::class)) {
        $payment = $modx->getObject(\MiniShop3\Model\msPayment::class, ['name' => 'Halyk ePay']);
        if (!$payment) {
            $payment = $modx->newObject(\MiniShop3\Model\msPayment::class);
            $payment->fromArray([
                'name' => 'Halyk ePay',
                'description' => 'Оплата банковской картой через Halyk ePay',
                'price' => '0',
                'position' => 1,
                'active' => 1,
                'class' => 'UniversalEpay\\Payment\\MiniShop3HalykPayment',
                'properties' => [],
            ], '', true, true);
            $payment->save();
        } else {
            $payment->set('class', 'UniversalEpay\\Payment\\MiniShop3HalykPayment');
            $payment->set('active', 1);
            $payment->save();
        }
    }
}
return true;

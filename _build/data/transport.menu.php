<?php
$menu = $modx->newObject('modMenu');
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
return $menu;

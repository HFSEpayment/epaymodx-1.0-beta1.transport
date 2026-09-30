<?php
namespace UniversalEpay\Adapter;

use UniversalEpay\Payment\OrderInterface;

interface ShopAdapterInterface
{
    public function load(string $orderId): ?OrderInterface;
}

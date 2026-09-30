<?php
namespace UniversalEpay\Adapter;

use UniversalEpay\Payment\OrderInterface;

final class MiniShop3Adapter implements ShopAdapterInterface
{
    public function __construct(private object $modx) {}

    public function load(string $orderId): ?OrderInterface
    {
        if (!$this->modx->getService('miniShop3')) {
            return null;
        }

        $order = $this->modx->getObject('msOrder', ['id' => (int)$orderId]);
        if (!$order) {
            $order = $this->modx->getObject('msOrder', ['num' => $orderId]);
        }
        return $order ? new MiniShop3Order($order) : null;
    }
}

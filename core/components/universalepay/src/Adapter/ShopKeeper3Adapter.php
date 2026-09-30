<?php
namespace UniversalEpay\Adapter;

use UniversalEpay\Payment\OrderInterface;
use UniversalEpay\Payment\GenericOrder;

final class ShopKeeper3Adapter implements ShopAdapterInterface
{
    public function __construct(private object $modx) {}

    public function load(string $orderId): ?OrderInterface
    {
        $model = $this->modx->getOption('core_path') . 'components/shopkeeper3/model/';
        $this->modx->addPackage('shopkeeper3', $model);

        $order = $this->modx->getObject('shk_order', (int)$orderId);
        return $order ? new ShopKeeper3Order($order) : null;
    }
}

<?php
namespace UniversalEpay\Adapter;

use UniversalEpay\Payment\OrderInterface;

final class MiniShop3Order implements OrderInterface
{
    public function __construct(private object $order) {}

    public function getOrderId(): string
    {
        return (string)$this->order->get('id');
    }

    public function getAmount(): float
    {
        $cost = (float)$this->order->get('cost');
        if ($cost > 0) {
            return round($cost, 2);
        }

        $cartCost = (float)$this->order->get('cart_cost');
        $deliveryCost = (float)$this->order->get('delivery_cost');
        $discountCost = (float)$this->order->get('discount_cost');
        $fallback = $cartCost + $deliveryCost - $discountCost;

        return round(max(0, $fallback), 2);
    }

    public function getCurrency(): string
    {
        return 'KZT';
    }

    public function getEmail(): ?string
    {
        $email = $this->order->get('useremail');
        return $email !== null && $email !== '' ? (string)$email : null;
    }

    public function getPhone(): ?string
    {
        $phone = $this->order->get('phone');
        return $phone !== null && $phone !== '' ? (string)$phone : null;
    }

    public function getDescription(): ?string
    {
        return 'Оплата заказа #' . $this->getOrderId();
    }

    public function markPaid(): bool
    {
        $status = (int)$this->order->getOption('universalepay_paid_status', null, 3);
        $this->order->set('status', $status);
        return (bool)$this->order->save();
    }

    public function markFailed(): bool
    {
        $status = (int)$this->order->getOption('universalepay_failed_status', null, 4);
        $this->order->set('status', $status);
        return (bool)$this->order->save();
    }
}

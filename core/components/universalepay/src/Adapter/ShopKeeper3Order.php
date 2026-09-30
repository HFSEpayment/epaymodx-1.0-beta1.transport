<?php
namespace UniversalEpay\Adapter;

use UniversalEpay\Payment\OrderInterface;

final class ShopKeeper3Order implements OrderInterface
{
    public function __construct(private object $order) {}

    public function getOrderId(): string { return (string)$this->order->get('id'); }
    public function getAmount(): float { return (float)$this->order->get('price'); }
    public function getCurrency(): string { return 'KZT'; }

    public function getEmail(): ?string
    {
        $contacts = @unserialize((string)$this->order->get('contacts'));
        $email = is_array($contacts) ? ($contacts['email'] ?? null) : null;
        return $email ? (string)$email : null;
    }

    public function getPhone(): ?string
    {
        $contacts = @unserialize((string)$this->order->get('contacts'));
        $phone = is_array($contacts) ? ($contacts['phone'] ?? null) : null;
        return $phone ? (string)$phone : null;
    }

    public function getDescription(): ?string { return 'Оплата заказа #' . $this->getOrderId(); }

    public function markPaid(): bool
    {
        $this->order->set('status', 6);
        return (bool)$this->order->save();
    }

    public function markFailed(): bool
    {
        $this->order->set('status', 5);
        return (bool)$this->order->save();
    }
}

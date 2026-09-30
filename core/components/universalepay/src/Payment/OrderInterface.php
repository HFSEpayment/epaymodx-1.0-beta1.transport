<?php
namespace UniversalEpay\Payment;

interface OrderInterface
{
    public function getOrderId(): string;
    public function getAmount(): float;
    public function getCurrency(): string;
    public function getEmail(): ?string;
    public function getPhone(): ?string;
    public function getDescription(): ?string;
    public function markPaid(): bool;
    public function markFailed(): bool;
}

<?php
namespace UniversalEpay\Payment;

final class GenericOrder implements OrderInterface
{
    public function __construct(
        private string $id,
        private float $amount,
        private string $currency = 'KZT',
        private ?string $email = null,
        private ?string $phone = null,
        private ?string $description = null
    ) {}

    public function getOrderId(): string { return $this->id; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getDescription(): ?string { return $this->description; }
    public function markPaid(): bool { return false; }
    public function markFailed(): bool { return false; }
}

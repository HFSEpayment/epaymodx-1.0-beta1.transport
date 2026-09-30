<?php
namespace UniversalEpay\Payment;

final class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $paymentId = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $message = null
    ) {}
}

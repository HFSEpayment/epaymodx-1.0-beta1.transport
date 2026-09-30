<?php
namespace UniversalEpay\Halyk;

use UniversalEpay\Payment\OrderInterface;

final class HalykClient
{
    private const TEST_OAUTH = 'https://testoauth.homebank.kz/epay2/oauth2/token';
    private const PROD_OAUTH = 'https://epay-oauth.homebank.kz/oauth2/token';
    private const TEST_JS = 'https://test-epay.homebank.kz/payform/payment-api.js';
    private const PROD_JS = 'https://epay.homebank.kz/payform/payment-api.js';
    private const TEST_STATUS = 'https://testepay.homebank.kz/api/check-status/payment/transaction/';
    private const PROD_STATUS = 'https://epay-api.homebank.kz/check-status/payment/transaction/';

    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $terminal,
        private bool $test = true,
        private string $currency = 'KZT',
        private string $language = 'rus'
    ) {}

    public function authorize(OrderInterface $order, string $postLink, string $failurePostLink, string $secretHash = ''): array
    {
        $invoice = $this->invoiceId($order->getOrderId());

        $fields = [
            'grant_type' => 'client_credentials',
            'scope' => 'payment',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'invoiceID' => $invoice,
            'amount' => number_format($order->getAmount(), 2, '.', ''),
            'currency' => $this->currency,
            'terminal' => $this->terminal,
            'postLink' => $postLink,
            'failurePostLink' => $failurePostLink,
        ];
        if ($secretHash !== '') {
            $fields['secret_hash'] = $secretHash;
        }

        $response = $this->request(
            $this->test ? self::TEST_OAUTH : self::PROD_OAUTH,
            $fields
        );

        if ($response['code'] !== 200 || empty($response['json']['access_token'])) {
            throw new \RuntimeException(
                'Halyk OAuth error: HTTP ' . $response['code'] . ' ' . $response['body']
            );
        }

        return [
            'auth' => $response['json'],
            'invoiceId' => $invoice,
        ];
    }

    public function buildPaymentObject(
        OrderInterface $order,
        array $authData,
        string $backLink,
        string $failureBackLink,
        string $postLink,
        string $failurePostLink
    ): array {
        return [
            'invoiceId' => $authData['invoiceId'],
            'backLink' => $backLink,
            'failureBackLink' => $failureBackLink,
            'postLink' => $postLink,
            'failurePostLink' => $failurePostLink,
            'language' => $this->language,
            'description' => $order->getDescription() ?: 'Оплата в интернет магазине',
            'accountId' => $order->getOrderId(),
            'terminal' => $this->terminal,
            'amount' => (float)number_format($order->getAmount(), 2, '.', ''),
            'currency' => $this->currency,
            'phone' => $order->getPhone() ?: '',
            'email' => $order->getEmail() ?: '',
            'auth' => $authData['auth'],
        ];
    }

    public function paymentJsUrl(): string
    {
        return $this->test ? self::TEST_JS : self::PROD_JS;
    }

    public function invoiceId(string $id): string
    {
        $digits = preg_replace('/\D+/', '', $id) ?: '0';
        $digits = substr($digits, -15);
        return str_pad($digits, 6, '0', STR_PAD_LEFT);
    }

    public function checkStatus(string $invoiceId): array
    {
        $token = $this->request(
            $this->test ? self::TEST_OAUTH : self::PROD_OAUTH,
            [
                'grant_type' => 'client_credentials',
                'scope' => 'payment',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'terminal' => $this->terminal,
            ]
        );

        if ($token['code'] !== 200 || empty($token['json']['access_token'])) {
            throw new \RuntimeException('Halyk status OAuth error: HTTP ' . $token['code']);
        }

        $url = ($this->test ? self::TEST_STATUS : self::PROD_STATUS) . rawurlencode($invoiceId);
        $response = $this->request($url, [], [
            'Authorization: Bearer ' . $token['json']['access_token'],
            'Accept: application/json',
        ], 'GET');

        if ($response['code'] !== 200 || !is_array($response['json'])) {
            throw new \RuntimeException('Halyk status error: HTTP ' . $response['code'] . ' ' . $response['body']);
        }

        return $response['json'];
    }

    public function isSuccessfulStatus(array $status, float $expectedAmount, string $expectedCurrency): bool
    {
        if ((string)($status['resultCode'] ?? '') !== '100') {
            return false;
        }

        $tx = $status['transaction'] ?? null;
        if (!is_array($tx)) {
            return false;
        }

        $statusName = strtoupper((string)($tx['statusName'] ?? ''));
        $reasonCode = (string)($tx['reasonCode'] ?? '');
        $amount = (float)($tx['amount'] ?? -1);
        $currency = strtoupper((string)($tx['currency'] ?? ''));

        // REFUND is explicitly not a successful payment state.
        if ($statusName === 'REFUND') {
            return false;
        }

        return $reasonCode === '00'
            && abs($amount - $expectedAmount) < 0.005
            && $currency === strtoupper($expectedCurrency);
    }

    private function request(string $url, array $fields = [], array $headers = [], string $method = 'POST'): array
    {
        $ch = curl_init($url);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($fields);
            if (!$headers) {
                $options[CURLOPT_HTTPHEADER] = [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/json',
                ];
            }
        } else {
            $options[CURLOPT_HTTPGET] = true;
        }

        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            throw new \RuntimeException('Halyk HTTP error: ' . $error);
        }

        $json = json_decode($body, true);

        return [
            'code' => $code,
            'body' => $body,
            'json' => is_array($json) ? $json : null,
        ];
    }
}

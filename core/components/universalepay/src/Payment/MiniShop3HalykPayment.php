<?php
namespace UniversalEpay\Payment;

use MiniShop3\Controllers\Payment\PaymentProviderInterface;
use MiniShop3\Model\msOrder;
use MiniShop3\Model\msPayment;
use UniversalEpay\Adapter\MiniShop3Order;
use UniversalEpay\Halyk\HalykClient;

final class MiniShop3HalykPayment implements PaymentProviderInterface
{
    private object $ms3;
    private object $modx;

    public function __construct(object $ms3, array $config = [])
    {
        $this->ms3 = $ms3;
        $this->modx = $this->resolveModx($ms3);
    }

    public function send(msOrder $order): array
    {
        $settings = $this->settings();
        $clientId = $settings['client_id'];
        $clientSecret = $settings['client_secret'];
        $terminal = $settings['terminal'];

        if (!$clientId || !$clientSecret || !$terminal) {
            return [
                'success' => false,
                'message' => 'UniversalEpay: заполните Client ID, Client Secret и Terminal ID в Extras → UniversalEpay.',
            ];
        }

        $adapter = new MiniShop3Order($order);
        $client = new HalykClient(
            $clientId,
            $clientSecret,
            $terminal,
            $settings['test_mode'],
            $settings['currency'],
            $settings['language']
        );

        $base = rtrim((string)$this->modx->getOption('site_url'), '/');
        $orderId = (string)$order->get('id');
        $amount = $adapter->getAmount();
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'UniversalEpay: сумма заказа равна 0. Проверьте итоговую сумму заказа в MiniShop3.',
            ];
        }

        $thanksId = (int)$this->modx->getOption('ms3_order_redirect_thanks_id', null, 1);
        $returnUrl = $thanksId > 0 ? $this->modx->makeUrl($thanksId) : ($base . '/');
        $success = $returnUrl;
        $fail = $returnUrl;
        $post = $base . '/assets/components/universalepay/callback.php';

        try {
            $secretHash = bin2hex(random_bytes(16));
            $auth = $client->authorize($adapter, $post, $post, $secretHash);
            $payment = $client->buildPaymentObject($adapter, $auth, $success, $fail, $post, $post);

            $_SESSION['universalepay_payment'][$orderId] = [
                'created' => time(),
                'payment' => $payment,
                'test' => $settings['test_mode'],
                'invoiceId' => $auth['invoiceId'],
                'amount' => $amount,
                'currency' => $settings['currency'],
                'secretHash' => $secretHash,
            ];

            return [
                'success' => true,
                'message' => '',
                'data' => [
                    'redirect' => $base . '/assets/components/universalepay/payment.php?order=' . rawurlencode($orderId),
                ],
            ];
        } catch (\Throwable $e) {
            $this->modx->log(
                \MODX\Revolution\modX::LOG_LEVEL_ERROR,
                '[UniversalEpay] ' . $e->getMessage()
            );
            return [
                'success' => false,
                'message' => 'Не удалось создать платёж Halyk ePay. Подробности в журнале MODX.',
            ];
        }
    }

    public function receive(msOrder $order): array
    {
        return ['success' => false, 'message' => 'Payment callback is handled by UniversalEpay.'];
    }

    public function getCost(msOrder $order, msPayment $payment, float $cost): float
    {
        return 0.0;
    }

    public function getOrderHash(msOrder $order): string
    {
        return sha1('universalepay:' . $order->get('id'));
    }

    private function settings(): array
    {
        $test = (bool)$this->modx->getOption('universalepay.test_mode', null, true);
        $prefix = $test ? 'test' : 'prod';

        return [
            'test_mode' => $test,
            'client_id' => trim((string)$this->modx->getOption("universalepay.{$prefix}_client_id", null, '')),
            'client_secret' => trim((string)$this->modx->getOption("universalepay.{$prefix}_client_secret", null, '')),
            'terminal' => trim((string)$this->modx->getOption("universalepay.{$prefix}_terminal", null, '')),
            'currency' => strtoupper((string)$this->modx->getOption('universalepay.currency', null, 'KZT')),
            'language' => strtolower((string)$this->modx->getOption('universalepay.language', null, 'rus')),
        ];
    }

    private function resolveModx(object $ms3): object
    {
        if (isset($GLOBALS['modx']) && is_object($GLOBALS['modx'])) {
            return $GLOBALS['modx'];
        }

        try {
            $ref = new \ReflectionObject($ms3);
            foreach (['modx', 'xpdo'] as $property) {
                if ($ref->hasProperty($property)) {
                    $p = $ref->getProperty($property);
                    $p->setAccessible(true);
                    $value = $p->getValue($ms3);
                    if (is_object($value)) {
                        return $value;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        throw new \RuntimeException('UniversalEpay: MODX instance is unavailable.');
    }
}

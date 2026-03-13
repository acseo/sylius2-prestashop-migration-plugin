<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Resolver;

final class OrderStateResolver
{
    private array $stateMapping;

    private array $defaultMapping = [
        'order_state' => 'new',
        'checkout_state' => 'completed',
        'payment_state' => 'awaiting_payment',
        'shipping_state' => 'ready',
    ];

    public function __construct(array $stateMapping = [])
    {
        $this->stateMapping = $stateMapping;
    }

    public function resolveOrderState(int $prestashopStateId): string
    {
        return $this->stateMapping[$prestashopStateId]['order_state'] ?? $this->defaultMapping['order_state'];
    }

    public function resolveCheckoutState(int $prestashopStateId): string
    {
        return $this->stateMapping[$prestashopStateId]['checkout_state'] ?? $this->defaultMapping['checkout_state'];
    }

    public function resolvePaymentState(int $prestashopStateId): string
    {
        return $this->stateMapping[$prestashopStateId]['payment_state'] ?? $this->defaultMapping['payment_state'];
    }

    public function resolveShippingState(int $prestashopStateId): string
    {
        return $this->stateMapping[$prestashopStateId]['shipping_state'] ?? $this->defaultMapping['shipping_state'];
    }
}

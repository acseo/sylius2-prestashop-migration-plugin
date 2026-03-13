<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Payment;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class PaymentMethodModel implements ModelInterface
{
    /**
     * PrestaShop module ID (primary key).
     * Payment methods in PrestaShop are modules hooked to 'paymentOptions'.
     */
    #[Field(source: 'id_module', target: 'prestashopId', id: true)]
    public int $id;

    /**
     * Module technical name (e.g., 'ps_wirepayment', 'ps_checkpayment').
     */
    #[Field(source: 'name')]
    public string $name;

    /**
     * Module active status.
     */
    #[Field(source: 'active')]
    public int $active;

    /**
     * Comma-separated list of currency ISO codes supported by this payment method.
     * Can be null if all currencies are accepted.
     */
    #[Field(source: 'currencies')]
    public ?string $currencies = null;
}

<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Order;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class OrderModel implements ModelInterface
{
    #[Field(source: 'id_order', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'id_customer')]
    public int $customerId;

    #[Field(source: 'id_address_delivery')]
    public int $deliveryAddressId;

    #[Field(source: 'id_address_invoice')]
    public int $invoiceAddressId;

    #[Field(source: 'id_currency')]
    public int $currencyId;

    #[Field(source: 'id_shop')]
    public int $shopId;

    #[Field(source: 'id_lang')]
    public int $langId;

    #[Field(source: 'current_state')]
    public int $currentState;

    #[Field(source: 'reference')]
    public ?string $reference = null;

    #[Field(source: 'payment')]
    public ?string $paymentMethod = null;

    #[Field(source: 'total_paid')]
    public float $totalPaid;

    #[Field(source: 'total_products')]
    public float $totalProducts;

    #[Field(source: 'total_shipping')]
    public float $totalShipping;

    #[Field(source: 'date_add')]
    public string $createdAt;

    #[Field(source: 'date_upd')]
    public string $updatedAt;
}


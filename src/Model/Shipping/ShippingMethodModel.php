<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Shipping;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class ShippingMethodModel implements ModelInterface
{
    #[Field(source: 'id_carrier', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'id_reference')]
    public int $idReference;

    /** @var array<string, string> locale => name */
    #[Field(source: 'name', target: 'name', translatable: true)]
    public array $name;

    #[Field(source: 'active')]
    public bool $active;

    #[Field(source: 'deleted')]
    public bool $deleted;

    #[Field(source: 'is_free')]
    public bool $isFree;

    /** 0=weight, 1=price, 2=fixed */
    #[Field(source: 'shipping_method')]
    public int $shippingMethod;

    #[Field(source: 'max_weight')]
    public float $maxWeight;

    /** @var array<string, string> locale => delay (description) */
    #[Field(source: 'delay', target: 'description', translatable: true)]
    public array $delay;
}

<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Customer;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class CustomerGroupModel implements ModelInterface
{
    #[Field(source: 'id_group', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'reduction')]
    public float $reduction;

    #[Field(source: 'price_display_method')]
    public int $priceDisplayMethod;

    #[Field(source: 'show_prices')]
    public int $showPrices;

    /** @var array<string, string> locale code => name */
    #[Field(source: 'name', target: 'name', translatable: true)]
    public array $name;
}

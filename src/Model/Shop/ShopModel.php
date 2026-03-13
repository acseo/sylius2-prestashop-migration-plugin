<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Shop;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\ToggleableTrait;

class ShopModel implements ModelInterface
{
    use ToggleableTrait;

    #[Field(source: 'id_shop', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'name', target: 'name')]
    public string $name;
}

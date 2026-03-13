<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Carrier;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class CarrierModel implements ModelInterface
{
    #[Field(source: 'id_carrier', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'name')]
    public string $name;

    #[Field(source: 'delay', target: 'description', translatable: true)]
    public array $description;

}

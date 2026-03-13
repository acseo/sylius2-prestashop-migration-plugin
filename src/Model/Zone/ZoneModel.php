<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Zone;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class ZoneModel implements ModelInterface
{
    #[Field(source: 'id_zone', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'name', target: 'name')]
    public string $name;
}

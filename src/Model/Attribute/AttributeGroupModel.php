<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Attribute;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class AttributeGroupModel implements ModelInterface
{
    #[Field(source: 'id_attribute_group', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'position', target: 'position')]
    public int $position;

    #[Field(source: 'name', translatable: true)]
    public array $name;
}

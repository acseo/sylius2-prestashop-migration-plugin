<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Category;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\ToggleableTrait;
use ACSEO\PrestashopMigrationPlugin\Model\UrlModelTrait;

class CategoryModel implements ModelInterface
{
    use UrlModelTrait, ToggleableTrait;

    #[Field(source: 'id_category', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'id_parent')]
    public int $parent;

    #[Field(source: 'position', target: 'position')]
    public int $position;

    #[Field(source: 'name', target: 'name', translatable: true)]
    public array $name;

    #[Field(source: 'description', translatable: true)]
    public array $description;

}

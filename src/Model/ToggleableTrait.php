<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;

trait ToggleableTrait
{
    #[Field(source: 'active', target: 'enabled')]
    public bool $enabled;
}

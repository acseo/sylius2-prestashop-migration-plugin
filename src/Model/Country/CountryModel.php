<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Country;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\ToggleableTrait;

class CountryModel implements ModelInterface
{
    use ToggleableTrait;

    #[Field(source: 'id_country', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'iso_code', target: 'code')]
    public string $code;
}

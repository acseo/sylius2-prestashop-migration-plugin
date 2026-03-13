<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Currency;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class CurrencyModel implements ModelInterface
{
    #[Field(source: 'id_currency', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'iso_code', target: 'code')]
    public string $code;

}

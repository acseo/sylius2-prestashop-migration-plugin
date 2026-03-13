<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Lang;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

class LangModel implements ModelInterface
{
    #[Field(source: 'id_lang', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'locale', target: 'code')]
    public string $code;

}

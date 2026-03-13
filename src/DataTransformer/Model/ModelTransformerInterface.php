<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Model;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;

interface ModelTransformerInterface
{
    public function transform(array $data): ModelInterface;
}

<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model;

interface ModelMapperInterface
{
    public function map(array $data): ModelInterface;
}

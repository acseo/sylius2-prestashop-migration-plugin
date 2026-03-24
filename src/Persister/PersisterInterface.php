<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Persister;

interface PersisterInterface
{
    public function persist(array $data, bool $dryRun = false): PersistStatus;
}

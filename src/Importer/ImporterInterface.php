<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Importer;

interface ImporterInterface
{
    public function import(callable $callable = null, bool $dryRun = false): void;

    public function size(): int;

    public function getName(): string;
}

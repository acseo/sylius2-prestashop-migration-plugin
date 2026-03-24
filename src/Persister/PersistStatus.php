<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Persister;

enum PersistStatus: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case SKIPPED = 'skipped';
    case FAILED = 'failed';
}

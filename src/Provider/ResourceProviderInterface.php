<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Provider;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ResourceProviderInterface
{
    public function getResource(ModelInterface $model): ResourceInterface;
}

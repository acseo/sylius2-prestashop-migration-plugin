<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ResourceTransformerInterface
{
    public function transform(ModelInterface $model): ResourceInterface;
}

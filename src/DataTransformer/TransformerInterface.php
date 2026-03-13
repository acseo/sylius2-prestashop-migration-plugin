<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer;

use Sylius\Component\Resource\Model\ResourceInterface;

interface TransformerInterface
{
    public function transform(array $data): ResourceInterface;
}

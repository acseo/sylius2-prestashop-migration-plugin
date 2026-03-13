<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Model;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\ModelMapperInterface;

final class ModelTransformer implements ModelTransformerInterface
{
    private ModelMapperInterface $mapper;

    public function __construct(ModelMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    public function transform(array $data): ModelInterface
    {
        return $this->mapper->map($data);
    }
}

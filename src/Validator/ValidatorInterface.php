<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ValidatorInterface
{
    public function validate(ResourceInterface $resource): bool;
}

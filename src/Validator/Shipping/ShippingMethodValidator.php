<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Shipping;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class ShippingMethodValidator implements ValidatorInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(ResourceInterface $resource): bool
    {
        return $this->validator->validate($resource);
    }
}

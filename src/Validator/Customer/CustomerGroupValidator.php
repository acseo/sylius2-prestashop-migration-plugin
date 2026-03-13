<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Customer;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class CustomerGroupValidator implements ValidatorInterface
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

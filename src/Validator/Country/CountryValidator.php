<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Country;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class CountryValidator implements ValidatorInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param CountryInterface $resource
     *
     * @return bool
     */
    public function validate(ResourceInterface $resource): bool
    {
        // If the country already exists (has an ID), skip validation
        // This means we're updating an existing country with its prestashop_id
        if ($resource->getId() !== null) {
            return $resource->isEnabled();
        }

        return $this->validator->validate($resource);
    }

}

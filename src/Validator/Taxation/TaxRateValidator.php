<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Taxation;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class TaxRateValidator implements ValidatorInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param TaxRateInterface $resource
     *
     * @return bool
     */
    public function validate(ResourceInterface $resource): bool
    {
        return null !== $resource->getZone() && $this->validator->validate($resource);
    }

}

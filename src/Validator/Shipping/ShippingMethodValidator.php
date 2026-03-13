<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Shipping;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;
use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class ShippingMethodValidator implements ValidatorInterface
{
    private ValidatorInterface $validator;

    private EntityRepositoryInterface $carrierRepository;

    public function __construct(ValidatorInterface $validator, EntityRepositoryInterface $carrierRepository)
    {
        $this->validator = $validator;
        $this->carrierRepository = $carrierRepository;
    }

    public function validate(ResourceInterface $resource): bool
    {
        
        return $this->validator->validate($resource);
    }

}

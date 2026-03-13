<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Taxation;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;

class TaxCategoryValidator implements ValidatorInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param TaxCategoryInterface $resource
     *
     * @return bool
     */
    public function validate(ResourceInterface $resource): bool
    {
        return $this->validator->validate($resource) ;
    }

}

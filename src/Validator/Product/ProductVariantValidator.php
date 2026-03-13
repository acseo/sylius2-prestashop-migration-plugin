<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Product;

use ACSEO\PrestashopMigrationPlugin\Entity\Product\ProductVariantInterface;
use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;

use Sylius\Component\Resource\Model\ResourceInterface;

class ProductVariantValidator implements ValidatorInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param ProductVariantInterface $resource
     *
     * @return bool
     */
    public function validate(ResourceInterface $resource): bool
    {
        return $resource->hasProduct() && $this->validator->validate($resource);
    }
}

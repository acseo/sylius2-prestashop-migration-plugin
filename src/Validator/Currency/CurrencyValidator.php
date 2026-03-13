<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Currency;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class CurrencyValidator implements ValidatorInterface
{
    private ValidatorInterface $decorated;

    public function __construct(ValidatorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function validate(ResourceInterface $resource): bool
    {
        // If the currency already exists (has an ID), skip validation
        // This means we're updating an existing currency with its prestashop_id
        if ($resource->getId() !== null) {
            return true;
        }

        // Otherwise, use the standard validation
        return $this->decorated->validate($resource);
    }
}

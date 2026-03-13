<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Validator\Locale;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class LocaleValidator implements ValidatorInterface
{
    private ValidatorInterface $decorated;

    public function __construct(ValidatorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function validate(ResourceInterface $resource): bool
    {
        // If the locale already exists (has an ID), skip validation
        // This means we're updating an existing locale with its prestashop_id
        if ($resource->getId() !== null) {
            return true;
        }

        // Otherwise, use the standard validation
        return $this->decorated->validate($resource);
    }
}

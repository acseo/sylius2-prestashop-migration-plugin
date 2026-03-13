<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Mock;

use ACSEO\PrestashopMigrationPlugin\Entity\PrestashopTrait;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;

class ShippingMethodWithPrestashopTrait extends BaseShippingMethod
{
    use PrestashopTrait;
}

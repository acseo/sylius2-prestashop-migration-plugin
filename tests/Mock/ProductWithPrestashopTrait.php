<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Mock;

use ACSEO\PrestashopMigrationPlugin\Entity\PrestashopTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

/**
 * Classe de test qui étend Product et utilise PrestashopTrait
 * Utilisée dans les tests d'intégration pour avoir accès aux méthodes
 * setPrestashopId(), getPrestashopId(), etc.
 */
class ProductWithPrestashopTrait extends BaseProduct
{
    use PrestashopTrait;
}

<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Entity\Product;


interface ProductVariantInterface
{
    public function hasProduct(): bool;
}

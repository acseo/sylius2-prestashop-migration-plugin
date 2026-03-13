<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Shipping;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;

class ShippingMethodRepository extends EntityRepository
{
    public function getCarrierZones(int $carrierId): array
    {
        $query = $this->createQueryBuilder();
        $query
            ->select('*')
            ->from($this->getTable() . '_zone')
            ->where($query->expr()->eq('id_carrier', $carrierId));

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getCarrierShops(int $carrierId): array
    {
        $query = $this->createQueryBuilder();
        $query
            ->select('*')
            ->from($this->getTableChannel())
            ->where($query->expr()->eq('id_carrier', $carrierId));

        return $query->executeQuery()->fetchAllAssociative();
    }
}

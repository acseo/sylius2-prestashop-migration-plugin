<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Carrier;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;

class CarrierRepository extends EntityRepository
{
    public function getZones(int $carrierId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getTable().'_zone')
            ->where($query->expr()->eq('id_carrier', $carrierId));

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getShops(int $carrierId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getTableChannel())
            ->where($query->expr()->eq('id_carrier', $carrierId));

        return $query->executeQuery()->fetchAllAssociative();
    }
}

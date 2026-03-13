<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Country;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;

class CountryRepository extends EntityRepository
{
    public function findByZoneId(int $zoneId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getTable())
            ->where($query->expr()->eq('id_zone', $zoneId));

        return $query->executeQuery()->fetchAllAssociative();
    }
}

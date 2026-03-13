<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Address;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;

interface AddressEntityRepositoryInterface extends EntityRepositoryInterface
{
    public function findCustomerAddresses(int $limit = null, int $offset = null): array;

    public function countCustomerAddresses(): int;
}

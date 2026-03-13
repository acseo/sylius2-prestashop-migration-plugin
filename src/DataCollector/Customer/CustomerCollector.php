<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataCollector\Customer;

use ACSEO\PrestashopMigrationPlugin\DataCollector\DataCollectorInterface;
use ACSEO\PrestashopMigrationPlugin\Repository\Customer\CustomerRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;

class CustomerCollector implements DataCollectorInterface
{
    /**
     * @var CustomerRepository $repository
     */
    private EntityRepositoryInterface $repository;

    public function __construct(EntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function collect(int $limit, int $offset): array
    {
        return $this->repository->findAllNotGuest($limit, $offset);
    }

    public function size(): int
    {
        return $this->repository->countAllNotGuest();
    }
}

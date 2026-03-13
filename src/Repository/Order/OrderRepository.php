<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Order;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;

class OrderRepository extends EntityRepository
{
    public function getOrderItems(int $orderId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getPrefix() . 'order_detail')
            ->where($query->expr()->eq('id_order', $orderId));

        return $query->executeQuery()->fetchAllAssociative();
    }
}


<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Product;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;

class ProductAttributeRepository extends EntityRepository
{
    public function getAttributesByProductId(int $productId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select($this->getCombinationTable().'.*')
            ->from($this->getCombinationTable())
            ->join(
                $this->getCombinationTable(),
                $this->getTable(),
                $this->getTable(),
                $query->expr()->comparison($this->getTable().'.'.$this->getPrimaryKey(), '=', $this->getCombinationTable().'.'.$this->getPrimaryKey()))
            ->where($query->expr()->eq($this->getTable().'.id_product', $productId));

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getAttributes(int $productAttributeId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select($this->getCombinationTable().'.*')
            ->from($this->getCombinationTable())
            ->join(
                $this->getCombinationTable(),
                $this->getTable(),
                $this->getTable(),
                $query->expr()->comparison($this->getTable().'.'.$this->getPrimaryKey(), '=', $this->getCombinationTable().'.'.$this->getPrimaryKey()))
            ->where($query->expr()->eq($this->getCombinationTable().'.id_product_attribute', $productAttributeId));

        return $query->executeQuery()->fetchAllAssociative();
    }

    private function getCombinationTable()
    {
        return $this->getTable().'_combination';
    }
}

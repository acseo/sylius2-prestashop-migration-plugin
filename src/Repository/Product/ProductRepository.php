<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Product;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;
use function Doctrine\DBAL\Query\QueryBuilder;

class ProductRepository extends EntityRepository
{
    public function findByReference(string $reference): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getTable())
            ->where($query->expr()->like("reference", $query->expr()->literal($reference)));

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function findBySlug(string $slug): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getTableTranslation())
            ->where($query->expr()->like("link_rewrite", $query->expr()->literal($slug)));

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getCategories(int $productId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getPrefix().'category_product')
            ->where($query->expr()->eq("id_product", $productId));

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getShops(int $productId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getTableChannel())
            ->where($query->expr()->eq("id_product", $productId));

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getImages(int $productId): array
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('*')
            ->from($this->getPrefix().'image')
            ->where($query->expr()->eq("id_product", $productId))
            ->orderBy('position', 'ASC');

        return $query->executeQuery()->fetchAllAssociative();
    }

    public function getPriceByShopId(int $productId, int $shopId): float
    {
        $query = $this->createQueryBuilder();

        $query
            ->select('price')
            ->from($this->getTableChannel())
            ->where($query->expr()->eq('id_product', $productId))
            ->andWhere($query->expr()->eq('id_shop', $shopId));

        return (float) $query->executeQuery()->fetchOne();

    }
}

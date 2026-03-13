<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Repository\Payment;

use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;

class PaymentMethodRepository extends EntityRepository
{
    /**
     * Récupère toutes les méthodes de paiement avec pagination.
     *
     * Les méthodes de paiement dans PrestaShop sont des modules qui s'accrochent
     * au hook 'paymentOptions'. Cette méthode récupère tous les modules de paiement actifs.
     *
     * @param int|null $limit
     * @param int|null $offset
     * @return array<int, array{id_module: int, name: string, displayName: string, active: int, currencies: string}>
     */
    public function findAll(int $limit = null, int $offset = null): array
    {
        $query = sprintf(
            'SELECT m.id_module, m.name, m.active, GROUP_CONCAT(c.iso_code) as currencies
             FROM %shook_module h
             JOIN %smodule m ON m.id_module = h.id_module
             LEFT JOIN %smodule_currency mc ON mc.id_module = m.id_module
             LEFT JOIN %scurrency c ON c.id_currency = mc.id_currency
             WHERE h.id_hook IN (SELECT id_hook FROM %shook WHERE name = "paymentOptions")
               AND m.active = 1
             GROUP BY m.id_module
             ORDER BY m.name',
            $this->getPrefix(),
            $this->getPrefix(),
            $this->getPrefix(),
            $this->getPrefix(),
            $this->getPrefix()
        );

        if (null !== $limit && null !== $offset) {
            $query .= sprintf(' LIMIT %d OFFSET %d', $limit, $offset);
        }

        return $this->getConnection()->fetchAllAssociative($query);
    }

    /**
     * Compte le nombre de modules de paiement actifs.
     *
     * @return int
     */
    public function count(): int
    {
        $query = sprintf(
            'SELECT COUNT(DISTINCT m.id_module)
             FROM %shook_module h
             JOIN %smodule m ON m.id_module = h.id_module
             WHERE h.id_hook IN (SELECT id_hook FROM %shook WHERE name = "paymentOptions")
               AND m.active = 1',
            $this->getPrefix(),
            $this->getPrefix(),
            $this->getPrefix()
        );

        return (int) $this->getConnection()->fetchOne($query);
    }
}

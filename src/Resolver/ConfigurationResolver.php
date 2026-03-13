<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Resolver;

use ACSEO\PrestashopMigrationPlugin\Repository\Configuration\ConfigurationRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;

class ConfigurationResolver
{
    /**
     * @var ConfigurationRepository
     */
    private EntityRepositoryInterface $configurationRepository;

    public function __construct(EntityRepositoryInterface $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function hasStockEnabled(): bool
    {
        return $this->configurationRepository->getStockEnabled();
    }
}

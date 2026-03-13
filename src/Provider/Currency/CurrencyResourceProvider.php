<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Provider\Currency;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CurrencyResourceProvider implements ResourceProviderInterface
{
    private ResourceProviderInterface $decorated;

    private RepositoryInterface $currencyRepository;

    public function __construct(
        ResourceProviderInterface $decorated,
        RepositoryInterface $currencyRepository
    ) {
        $this->decorated = $decorated;
        $this->currencyRepository = $currencyRepository;
    }

    public function getResource(ModelInterface $model): ResourceInterface
    {
        // Try to find by prestashopId first (default behavior)
        $resource = $this->decorated->getResource($model);

        // If resource was just created (no ID yet) and has a code property
        if ($resource->getId() === null && property_exists($model, 'code')) {
            // Try to find existing currency by code
            $existingCurrency = $this->currencyRepository->findOneBy(['code' => $model->code]);

            if ($existingCurrency !== null) {
                // Use the existing currency instead of creating a new one
                return $existingCurrency;
            }
        }

        return $resource;
    }
}

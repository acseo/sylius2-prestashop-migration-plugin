<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Provider\Country;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CountryResourceProvider implements ResourceProviderInterface
{
    private ResourceProviderInterface $decorated;

    private RepositoryInterface $countryRepository;

    public function __construct(
        ResourceProviderInterface $decorated,
        RepositoryInterface $countryRepository
    ) {
        $this->decorated = $decorated;
        $this->countryRepository = $countryRepository;
    }

    public function getResource(ModelInterface $model): ResourceInterface
    {
        // Try to find by prestashopId first (default behavior)
        $resource = $this->decorated->getResource($model);

        // If resource was just created (no ID yet) and has a code property
        if ($resource->getId() === null && property_exists($model, 'code')) {
            // Try to find existing country by ISO code
            $existingCountry = $this->countryRepository->findOneBy(['code' => $model->code]);

            if ($existingCountry !== null) {
                // Use the existing country instead of creating a new one
                return $existingCountry;
            }
        }

        return $resource;
    }
}

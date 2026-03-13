<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Provider\Locale;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class LocaleResourceProvider implements ResourceProviderInterface
{
    private ResourceProviderInterface $decorated;

    private RepositoryInterface $localeRepository;

    public function __construct(
        ResourceProviderInterface $decorated,
        RepositoryInterface $localeRepository
    ) {
        $this->decorated = $decorated;
        $this->localeRepository = $localeRepository;
    }

    public function getResource(ModelInterface $model): ResourceInterface
    {
        // Try to find by prestashopId first (default behavior)
        $resource = $this->decorated->getResource($model);

        // If resource was just created (no ID yet) and has a code property
        if ($resource->getId() === null && property_exists($model, 'code')) {
            // Transform the code the same way LangResourceTransformer does
            $transformedCode = StringInflector::nameToCode($model->code);

            // Try to find existing locale by the transformed code
            $existingLocale = $this->localeRepository->findOneBy(['code' => $transformedCode]);

            if ($existingLocale !== null) {
                // Use the existing locale instead of creating a new one
                return $existingLocale;
            }
        }

        return $resource;
    }
}

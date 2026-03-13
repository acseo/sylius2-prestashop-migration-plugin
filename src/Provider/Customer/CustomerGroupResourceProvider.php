<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Provider\Customer;

use ACSEO\PrestashopMigrationPlugin\Model\Customer\CustomerGroupModel;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use Behat\Transliterator\Transliterator;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CustomerGroupResourceProvider implements ResourceProviderInterface
{
    private ResourceProviderInterface $decorated;

    private RepositoryInterface $customerGroupRepository;

    public function __construct(
        ResourceProviderInterface $decorated,
        RepositoryInterface $customerGroupRepository
    ) {
        $this->decorated = $decorated;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    public function getResource(ModelInterface $model): ResourceInterface
    {
        $resource = $this->decorated->getResource($model);

        // If resource was just created (no ID yet), try to find existing by code to avoid duplicates
        if ($resource->getId() === null && $model instanceof CustomerGroupModel) {
            $name = $this->getFirstNonEmptyName($model);
            if (null !== $name) {
                $code = StringInflector::nameToLowercaseCode(
                    Transliterator::transliterate(sprintf('%s %s', $name, (string) $model->id))
                );
                $existing = $this->customerGroupRepository->findOneBy(['code' => $code]);
                if (null !== $existing) {
                    return $existing;
                }
            }
        }

        return $resource;
    }

    private function getFirstNonEmptyName(CustomerGroupModel $model): ?string
    {
        if (empty($model->name)) {
            return null;
        }
        foreach ($model->name as $value) {
            if (null !== $value && '' !== trim($value)) {
                return trim($value);
            }
        }
        return trim((string) reset($model->name));
    }
}

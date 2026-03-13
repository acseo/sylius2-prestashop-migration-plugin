<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Customer;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Customer\CustomerGroupModel;
use ACSEO\PrestashopMigrationPlugin\Model\LocaleFetcher;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use Behat\Transliterator\Transliterator;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Customer\Model\CustomerGroupInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class CustomerGroupResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;

    private LocaleFetcher $localeFetcher;

    private LoggerInterface $logger;

    public function __construct(
        ResourceTransformerInterface $transformer,
        LocaleFetcher $localeFetcher,
        LoggerInterface $logger
    ) {
        $this->transformer = $transformer;
        $this->localeFetcher = $localeFetcher;
        $this->logger = $logger;
    }

    public function transform(ModelInterface $model): ResourceInterface
    {
        /** @var CustomerGroupModel $model */
        /** @var CustomerGroupInterface $group */
        $group = $this->transformer->transform($model);

        // Sylius CustomerGroup is not translatable: use first available locale for name/code
        $name = $this->getDisplayName($model);
        if (null !== $name) {
            $group->setName($name);
        }

        if (null === $group->getCode() || '' === $group->getCode()) {
            $code = StringInflector::nameToLowercaseCode(
                Transliterator::transliterate(sprintf('%s %s', $name ?? 'group', (string) $model->id))
            );
            $group->setCode($code);
        }

        // Log groups with reduction for manual configuration (Sylius uses promotions)
        if ($model->reduction > 0) {
            $this->logger->info('PrestaShop CustomerGroup has reduction (not migrated to Sylius)', [
                'prestashop_id' => $model->id,
                'name' => $name,
                'reduction' => $model->reduction,
            ]);
        }

        return $group;
    }

    private function getDisplayName(CustomerGroupModel $model): ?string
    {
        $locales = $this->localeFetcher->getLocales();
        foreach ($locales as $locale) {
            $code = $locale->getCode();
            if (isset($model->name[$code]) && '' !== trim((string) $model->name[$code])) {
                return trim((string) $model->name[$code]);
            }
        }
        if (!empty($model->name)) {
            return trim((string) reset($model->name));
        }
        return null;
    }
}

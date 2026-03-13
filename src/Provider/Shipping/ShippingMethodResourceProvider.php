<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Provider\Shipping;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Shipping\ShippingMethodModel;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use ACSEO\PrestashopMigrationPlugin\Repository\Shipping\ShippingMethodRepository;
use Behat\Transliterator\Transliterator;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ShippingMethodResourceProvider implements ResourceProviderInterface
{
    private ResourceProviderInterface $decorated;
    private RepositoryInterface $shippingMethodRepository;
    private ShippingMethodRepository $carrierRepository;

    public function __construct(
        ResourceProviderInterface $decorated,
        RepositoryInterface $shippingMethodRepository,
        ShippingMethodRepository $carrierRepository
    ) {
        $this->decorated = $decorated;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->carrierRepository = $carrierRepository;
    }

    public function getResource(ModelInterface $model): ResourceInterface
    {
        $resource = $this->decorated->getResource($model);

        if ($resource->getId() === null && $model instanceof ShippingMethodModel) {
            $code = $this->generateCode($model);
            $existing = $this->shippingMethodRepository->findOneBy(['code' => $code]);
            if (null !== $existing) {
                return $existing;
            }
        }

        return $resource;
    }

    private function generateCode(ShippingMethodModel $model): string
    {
        $name = $this->getFirstNonEmpty($model->name);
        $base = Transliterator::transliterate($name ?? 'carrier');
        $code = StringInflector::nameToLowercaseCode($base . '_' . $model->id);
        $zones = $this->carrierRepository->getCarrierZones($model->id);
        if (count($zones) > 1) {
            $zoneIds = array_map(fn (array $row) => (int) $row['id_zone'], $zones);
            sort($zoneIds);
            $code .= '_z' . implode('_', $zoneIds);
        }
        return $code;
    }

    private function getFirstNonEmpty(array $values): ?string
    {
        foreach ($values as $v) {
            if (null !== $v && '' !== trim((string) $v)) {
                return trim((string) $v);
            }
        }
        return !empty($values) ? trim((string) reset($values)) : null;
    }
}

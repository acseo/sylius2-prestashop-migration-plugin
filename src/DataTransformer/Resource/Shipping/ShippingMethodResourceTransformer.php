<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Shipping;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\LocaleFetcher;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Shipping\ShippingMethodModel;
use ACSEO\PrestashopMigrationPlugin\Repository\Shipping\ShippingMethodRepository;
use Behat\Transliterator\Transliterator;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Calculator\DefaultCalculators;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;

final class ShippingMethodResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;
    private LocaleFetcher $localeFetcher;
    private ShippingMethodRepository $carrierRepository;
    private RepositoryInterface $zoneRepository;
    private RepositoryInterface $shippingCategoryRepository;
    private RepositoryInterface $channelRepository;

    public function __construct(
        ResourceTransformerInterface $transformer,
        LocaleFetcher $localeFetcher,
        ShippingMethodRepository $carrierRepository,
        RepositoryInterface $zoneRepository,
        RepositoryInterface $shippingCategoryRepository,
        RepositoryInterface $channelRepository
    ) {
        $this->transformer = $transformer;
        $this->localeFetcher = $localeFetcher;
        $this->carrierRepository = $carrierRepository;
        $this->zoneRepository = $zoneRepository;
        $this->shippingCategoryRepository = $shippingCategoryRepository;
        $this->channelRepository = $channelRepository;
    }

    public function transform(ModelInterface $model): ResourceInterface
    {
        /** @var ShippingMethodModel $model */
        /** @var ShippingMethodInterface $resource */
        $resource = $this->transformer->transform($model);

        $resource->setEnabled($model->active && !$model->deleted);

        $resource->setCalculator(DefaultCalculators::FLAT_RATE);

        $code = $this->generateCode($model);
        $resource->setCode($code);

        $this->setCategory($resource);
        $this->setTranslations($resource, $model);
        $this->setConfiguration($resource, $model);
        $this->addZone($resource);
        $this->addChannels($resource);

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

    private function setCategory(ShippingMethodInterface $resource): void
    {
        $category = $this->shippingCategoryRepository->findOneBy(['code' => 'standard']);
        if ($category instanceof ShippingCategoryInterface) {
            $resource->setCategory($category);
            return;
        }
        $category = $this->shippingCategoryRepository->findOneBy(['code' => 'default']);
        if ($category instanceof ShippingCategoryInterface) {
            $resource->setCategory($category);
            return;
        }
        $all = $this->shippingCategoryRepository->findAll();
        if (count($all) > 0 && $all[0] instanceof ShippingCategoryInterface) {
            $resource->setCategory($all[0]);
        }
    }

    private function setTranslations(ShippingMethodInterface $resource, ShippingMethodModel $model): void
    {
        $locales = $this->localeFetcher->getLocales();
        $fallbackName = $this->getFirstNonEmpty($model->name) ?? 'Carrier ' . $model->id;
        $fallbackDelay = $this->getFirstNonEmpty($model->delay);

        foreach ($locales as $locale) {
            $localeCode = $locale->getCode();
            $resource->setCurrentLocale($localeCode);
            $resource->setFallbackLocale($localeCode);

            $name = $model->name[$localeCode] ?? $fallbackName;
            $resource->setName(null !== $name && '' !== trim($name) ? trim($name) : $fallbackName);

            $description = $model->delay[$localeCode] ?? $fallbackDelay;
            if (null !== $description && '' !== trim($description)) {
                $resource->setDescription(trim($description));
            }
        }
    }

    private function setConfiguration(ShippingMethodInterface $resource, ShippingMethodModel $model): void
    {
        $channels = $this->channelRepository->findAll();
        $configuration = [];
        $amount = $model->isFree ? 0 : 1000;

        foreach ($channels as $channel) {
            if ($channel instanceof ChannelInterface) {
                $configuration[$channel->getCode()] = ['amount' => $amount];
            }
        }
        if ($configuration === []) {
            $configuration = ['default' => ['amount' => $amount]];
        }
        $resource->setConfiguration($configuration);
    }

    private function addZone(ShippingMethodInterface $resource): void
    {
        $carrierId = $resource->getPrestashopId();
        if (null === $carrierId) {
            return;
        }
        $carrierZones = $this->carrierRepository->getCarrierZones($carrierId);
        foreach ($carrierZones as $carrierZone) {
            $zoneId = (int) $carrierZone['id_zone'];
            $zone = $this->zoneRepository->findOneBy(['prestashopId' => $zoneId]);
            if ($zone instanceof ZoneInterface) {
                $resource->setZone($zone);
                return;
            }
        }
    }

    private function addChannels(ShippingMethodInterface $resource): void
    {
        $carrierId = $resource->getPrestashopId();
        if (null === $carrierId) {
            return;
        }
        $shops = $this->carrierRepository->getCarrierShops($carrierId);
        foreach ($shops as $shop) {
            $shopId = (int) $shop['id_shop'];
            $channel = $this->channelRepository->findOneBy(['prestashopId' => $shopId]);
            if ($channel instanceof ChannelInterface) {
                $resource->addChannel($channel);
            }
        }
        if ($resource->getChannels()->isEmpty()) {
            foreach ($this->channelRepository->findAll() as $channel) {
                if ($channel instanceof ChannelInterface) {
                    $resource->addChannel($channel);
                }
            }
        }
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

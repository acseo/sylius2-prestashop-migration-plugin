<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\DataTransformer\Resource\Shipping;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Shipping\ShippingMethodResourceTransformer;
use ACSEO\PrestashopMigrationPlugin\Model\LocaleFetcher;
use ACSEO\PrestashopMigrationPlugin\Model\Shipping\ShippingMethodModel;
use ACSEO\PrestashopMigrationPlugin\Repository\Shipping\ShippingMethodRepository;
use ACSEO\PrestashopMigrationPlugin\Tests\Mock\ShippingMethodWithPrestashopTrait;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Calculator\DefaultCalculators;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;

class ShippingMethodResourceTransformerTest extends TestCase
{
    private ResourceTransformerInterface $baseTransformer;
    private LocaleFetcher $localeFetcher;
    private ShippingMethodRepository $carrierRepository;
    private RepositoryInterface $zoneRepository;
    private RepositoryInterface $shippingCategoryRepository;
    private RepositoryInterface $channelRepository;
    private ShippingMethodResourceTransformer $transformer;

    protected function setUp(): void
    {
        $this->baseTransformer = $this->createMock(ResourceTransformerInterface::class);
        $this->localeFetcher = $this->createMock(LocaleFetcher::class);
        $this->carrierRepository = $this->createMock(ShippingMethodRepository::class);
        $this->zoneRepository = $this->createMock(RepositoryInterface::class);
        $this->shippingCategoryRepository = $this->createMock(RepositoryInterface::class);
        $this->channelRepository = $this->createMock(RepositoryInterface::class);

        $this->transformer = new ShippingMethodResourceTransformer(
            $this->baseTransformer,
            $this->localeFetcher,
            $this->carrierRepository,
            $this->zoneRepository,
            $this->shippingCategoryRepository,
            $this->channelRepository
        );
    }

    private function createModel(
        int $id = 1,
        array $name = ['en_US' => 'Colissimo'],
        bool $active = true,
        bool $deleted = false,
        bool $isFree = false,
        array $delay = ['en_US' => '2-3 days']
    ): ShippingMethodModel {
        $model = new ShippingMethodModel();
        $model->id = $id;
        $model->idReference = 1;
        $model->name = $name;
        $model->active = $active;
        $model->deleted = $deleted;
        $model->isFree = $isFree;
        $model->shippingMethod = 0;
        $model->maxWeight = 30.0;
        $model->delay = $delay;
        return $model;
    }

    private function createResource(int $prestashopId = 1): ShippingMethodWithPrestashopTrait
    {
        $resource = new ShippingMethodWithPrestashopTrait();
        $resource->setPrestashopId($prestashopId);
        return $resource;
    }

    private function setupLocales(array $localeCodes = ['en_US']): void
    {
        $locales = [];
        foreach ($localeCodes as $code) {
            $locale = $this->createMock(LocaleInterface::class);
            $locale->method('getCode')->willReturn($code);
            $locales[] = $locale;
        }
        $this->localeFetcher->method('getLocales')->willReturn($locales);
    }

    public function testTransformSimpleCarrier(): void
    {
        $model = $this->createModel(1, ['en_US' => 'Colissimo']);
        $resource = $this->createResource(1);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->with(1)->willReturn([['id_zone' => 1]]);
        $this->carrierRepository->method('getCarrierShops')->with(1)->willReturn([['id_shop' => 1]]);
        $this->shippingCategoryRepository->method('findOneBy')->with(['code' => 'standard'])->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->zoneRepository->method('findOneBy')->willReturn($this->createMock(ZoneInterface::class));
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getCode')->willReturn('default');
        $this->channelRepository->method('findOneBy')->willReturn($channel);
        $this->channelRepository->method('findAll')->willReturn([]);

        $result = $this->transformer->transform($model);

        self::assertSame($resource, $result);
        self::assertTrue($result->isEnabled());
        self::assertSame(DefaultCalculators::FLAT_RATE, $result->getCalculator());
        self::assertNotNull($result->getCode());
        self::assertStringContainsString('colissimo', $result->getCode());
        self::assertNotNull($result->getCategory());
        self::assertNotNull($result->getZone());
    }

    public function testTransformFreeCarrierSetsZeroAmount(): void
    {
        $model = $this->createModel(1, ['en_US' => 'Free shipping'], true, false, true);
        $resource = $this->createResource(1);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([['id_zone' => 1]]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->zoneRepository->method('findOneBy')->willReturn($this->createMock(ZoneInterface::class));
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getCode')->willReturn('default');
        $this->channelRepository->method('findAll')->willReturn([$channel]);

        $this->transformer->transform($model);

        $config = $resource->getConfiguration();
        self::assertArrayHasKey('default', $config);
        self::assertSame(0, $config['default']['amount']);
    }

    public function testTransformInactiveCarrierIsNotEnabled(): void
    {
        $model = $this->createModel(1, ['en_US' => 'Inactive'], false, false);
        $resource = $this->createResource(1);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        self::assertFalse($resource->isEnabled());
    }

    public function testTransformDeletedCarrierIsNotEnabled(): void
    {
        $model = $this->createModel(1, ['en_US' => 'Deleted'], true, true);
        $resource = $this->createResource(1);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        self::assertFalse($resource->isEnabled());
    }

    public function testTransformWithMultipleZonesGeneratesCodeWithZoneSuffix(): void
    {
        $model = $this->createModel(1, ['en_US' => 'Multi Zone']);
        $resource = $this->createResource(1);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->with(1)->willReturn([
            ['id_zone' => 2],
            ['id_zone' => 1],
        ]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->zoneRepository->method('findOneBy')->willReturn($this->createMock(ZoneInterface::class));
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        $code = $resource->getCode();
        self::assertNotNull($code);
        self::assertTrue(str_contains($code, '_z1_2') || str_contains($code, '_z2_1'), 'Code should contain zone suffix');
    }

    public function testTransformSetsTranslationsForMultipleLocales(): void
    {
        $model = $this->createModel(1, [
            'en_US' => 'Express',
            'fr_FR' => 'Express FR',
        ], true, false, false, [
            'en_US' => '1 day',
            'fr_FR' => '1 jour',
        ]);
        $resource = $this->createResource(1);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US', 'fr_FR']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([['id_zone' => 1]]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->zoneRepository->method('findOneBy')->willReturn($this->createMock(ZoneInterface::class));
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        $resource->setCurrentLocale('en_US');
        self::assertSame('Express', $resource->getName());
        $resource->setCurrentLocale('fr_FR');
        self::assertSame('Express FR', $resource->getName());
    }

    public function testTransformSetsCategoryWhenStandardExists(): void
    {
        $model = $this->createModel(1);
        $resource = $this->createResource(1);
        $category = $this->createMock(ShippingCategoryInterface::class);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->with(['code' => 'standard'])->willReturn($category);
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        self::assertSame($category, $resource->getCategory());
    }

    public function testTransformSetsFirstCategoryWhenNoStandardOrDefault(): void
    {
        $model = $this->createModel(1);
        $resource = $this->createResource(1);
        $firstCategory = $this->createMock(ShippingCategoryInterface::class);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn(null);
        $this->shippingCategoryRepository->method('findAll')->willReturn([$firstCategory]);
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        self::assertSame($firstCategory, $resource->getCategory());
    }

    public function testTransformCalculatorIsFlatRate(): void
    {
        $model = $this->createModel(1);
        $resource = $this->createResource(1);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        self::assertSame(DefaultCalculators::FLAT_RATE, $resource->getCalculator());
    }

    public function testTransformSetsFallbackNameWhenNameEmpty(): void
    {
        $model = $this->createModel(42, []);
        $resource = $this->createResource(42);

        $this->baseTransformer->method('transform')->with($model)->willReturn($resource);
        $this->setupLocales(['en_US']);
        $this->carrierRepository->method('getCarrierZones')->willReturn([]);
        $this->carrierRepository->method('getCarrierShops')->willReturn([]);
        $this->shippingCategoryRepository->method('findOneBy')->willReturn($this->createMock(ShippingCategoryInterface::class));
        $this->channelRepository->method('findAll')->willReturn([]);

        $this->transformer->transform($model);

        $resource->setCurrentLocale('en_US');
        self::assertSame('Carrier 42', $resource->getName());
    }
}

<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Provider\Shipping;

use ACSEO\PrestashopMigrationPlugin\Model\Shipping\ShippingMethodModel;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\Shipping\ShippingMethodResourceProvider;
use ACSEO\PrestashopMigrationPlugin\Repository\Shipping\ShippingMethodRepository;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ShippingMethodResourceProviderTest extends TestCase
{
    private ResourceProviderInterface $decorated;
    private RepositoryInterface $shippingMethodRepository;
    private ShippingMethodRepository $carrierRepository;
    private ShippingMethodResourceProvider $provider;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ResourceProviderInterface::class);
        $this->shippingMethodRepository = $this->createMock(RepositoryInterface::class);
        $this->carrierRepository = $this->createMock(ShippingMethodRepository::class);
        $this->provider = new ShippingMethodResourceProvider(
            $this->decorated,
            $this->shippingMethodRepository,
            $this->carrierRepository
        );
    }

    public function testReturnsExistingShippingMethodWhenFoundByCodeNoDuplication(): void
    {
        $model = new ShippingMethodModel();
        $model->id = 1;
        $model->name = ['en_US' => 'Colissimo'];

        $newResource = $this->createMock(ShippingMethodInterface::class);
        $newResource->method('getId')->willReturn(null);

        $existingResource = $this->createMock(ShippingMethodInterface::class);
        $existingResource->method('getId')->willReturn(5);

        $this->decorated
            ->expects(self::once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newResource);

        $this->carrierRepository
            ->expects(self::once())
            ->method('getCarrierZones')
            ->with(1)
            ->willReturn([['id_zone' => 1]]);

        $this->shippingMethodRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(self::callback(function (array $criteria): bool {
                return isset($criteria['code']) && str_contains($criteria['code'], 'colissimo');
            }))
            ->willReturn($existingResource);

        $result = $this->provider->getResource($model);

        self::assertSame($existingResource, $result);
    }

    public function testReturnsNewResourceWhenNotFoundByCode(): void
    {
        $model = new ShippingMethodModel();
        $model->id = 2;
        $model->name = ['en_US' => 'DHL'];

        $newResource = $this->createMock(ShippingMethodInterface::class);
        $newResource->method('getId')->willReturn(null);

        $this->decorated
            ->expects(self::once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newResource);

        $this->carrierRepository->method('getCarrierZones')->with(2)->willReturn([['id_zone' => 1]]);
        $this->shippingMethodRepository->method('findOneBy')->willReturn(null);

        $result = $this->provider->getResource($model);

        self::assertSame($newResource, $result);
    }

    public function testReturnsResourceDirectlyWhenAlreadyHasIdRelanceMigration(): void
    {
        $model = new ShippingMethodModel();
        $model->id = 1;
        $model->name = ['en_US' => 'Colissimo'];

        $existingResource = $this->createMock(ShippingMethodInterface::class);
        $existingResource->method('getId')->willReturn(10);

        $this->decorated
            ->expects(self::once())
            ->method('getResource')
            ->with($model)
            ->willReturn($existingResource);

        $this->shippingMethodRepository->expects(self::never())->method('findOneBy');

        $result = $this->provider->getResource($model);

        self::assertSame($existingResource, $result);
    }

    public function testReturnsExistingWhenMultipleZonesSameCode(): void
    {
        $model = new ShippingMethodModel();
        $model->id = 1;
        $model->name = ['en_US' => 'Multi Zone'];

        $newResource = $this->createMock(ShippingMethodInterface::class);
        $newResource->method('getId')->willReturn(null);

        $existingResource = $this->createMock(ShippingMethodInterface::class);
        $existingResource->method('getId')->willReturn(3);

        $this->decorated->method('getResource')->with($model)->willReturn($newResource);

        $this->carrierRepository
            ->method('getCarrierZones')
            ->with(1)
            ->willReturn([['id_zone' => 1], ['id_zone' => 2]]);

        $this->shippingMethodRepository
            ->method('findOneBy')
            ->with(self::callback(function (array $criteria): bool {
                return isset($criteria['code']) && str_contains($criteria['code'], '_z');
            }))
            ->willReturn($existingResource);

        $result = $this->provider->getResource($model);

        self::assertSame($existingResource, $result);
    }
}

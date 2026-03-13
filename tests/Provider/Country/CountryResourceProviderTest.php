<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Provider\Country;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\Country\CountryResourceProvider;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class CountryResourceProviderTest extends TestCase
{
    private ResourceProviderInterface $decorated;
    private RepositoryInterface $repository;
    private CountryResourceProvider $provider;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ResourceProviderInterface::class);
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->provider = new CountryResourceProvider($this->decorated, $this->repository);
    }

    public function testReturnsExistingCountryWhenFoundByCode(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'FR';
        };

        $newCountry = $this->createMock(CountryInterface::class);
        $newCountry->method('getId')->willReturn(null);

        $existingCountry = $this->createMock(CountryInterface::class);
        $existingCountry->method('getId')->willReturn(1);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newCountry);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'FR'])
            ->willReturn($existingCountry);

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($existingCountry, $result);
    }

    public function testReturnsNewCountryWhenNotFoundByCode(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'DE';
        };

        $newCountry = $this->createMock(CountryInterface::class);
        $newCountry->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newCountry);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'DE'])
            ->willReturn(null);

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($newCountry, $result);
    }

    public function testReturnsExistingCountryDirectlyWhenAlreadyHasId(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'IT';
        };

        $existingCountry = $this->createMock(CountryInterface::class);
        $existingCountry->method('getId')->willReturn(2);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($existingCountry);

        $this->repository
            ->expects($this->never())
            ->method('findOneBy');

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($existingCountry, $result);
    }

    public function testReturnsResourceWhenModelHasNoCodeProperty(): void
    {
        // Given
        $model = new class implements ModelInterface {
            // No code property
        };

        $country = $this->createMock(CountryInterface::class);
        $country->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($country);

        $this->repository
            ->expects($this->never())
            ->method('findOneBy');

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($country, $result);
    }
}

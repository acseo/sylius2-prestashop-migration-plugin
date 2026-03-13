<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Provider\Currency;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\Currency\CurrencyResourceProvider;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class CurrencyResourceProviderTest extends TestCase
{
    private ResourceProviderInterface $decorated;
    private RepositoryInterface $repository;
    private CurrencyResourceProvider $provider;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ResourceProviderInterface::class);
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->provider = new CurrencyResourceProvider($this->decorated, $this->repository);
    }

    public function testReturnsExistingCurrencyWhenFoundByCode(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'EUR';
        };

        $newCurrency = $this->createMock(CurrencyInterface::class);
        $newCurrency->method('getId')->willReturn(null);

        $existingCurrency = $this->createMock(CurrencyInterface::class);
        $existingCurrency->method('getId')->willReturn(1);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newCurrency);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'EUR'])
            ->willReturn($existingCurrency);

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($existingCurrency, $result);
    }

    public function testReturnsNewCurrencyWhenNotFoundByCode(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'USD';
        };

        $newCurrency = $this->createMock(CurrencyInterface::class);
        $newCurrency->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newCurrency);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'USD'])
            ->willReturn(null);

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($newCurrency, $result);
    }

    public function testReturnsExistingCurrencyDirectlyWhenAlreadyHasId(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'GBP';
        };

        $existingCurrency = $this->createMock(CurrencyInterface::class);
        $existingCurrency->method('getId')->willReturn(2);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($existingCurrency);

        $this->repository
            ->expects($this->never())
            ->method('findOneBy');

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($existingCurrency, $result);
    }

    public function testReturnsResourceWhenModelHasNoCodeProperty(): void
    {
        // Given
        $model = new class implements ModelInterface {
            // No code property
        };

        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($currency);

        $this->repository
            ->expects($this->never())
            ->method('findOneBy');

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($currency, $result);
    }
}

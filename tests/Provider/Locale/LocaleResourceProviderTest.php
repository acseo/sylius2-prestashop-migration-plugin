<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Provider\Locale;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\Locale\LocaleResourceProvider;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class LocaleResourceProviderTest extends TestCase
{
    private ResourceProviderInterface $decorated;
    private RepositoryInterface $repository;
    private LocaleResourceProvider $provider;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ResourceProviderInterface::class);
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->provider = new LocaleResourceProvider($this->decorated, $this->repository);
    }

    public function testReturnsExistingLocaleWhenFoundByCode(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'fr_FR';
        };

        $newLocale = $this->createMock(LocaleInterface::class);
        $newLocale->method('getId')->willReturn(null);

        $existingLocale = $this->createMock(LocaleInterface::class);
        $existingLocale->method('getId')->willReturn(123);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newLocale);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'fr_FR'])
            ->willReturn($existingLocale);

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($existingLocale, $result);
    }

    public function testReturnsNewLocaleWhenNotFoundByCode(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'en_US';
        };

        $newLocale = $this->createMock(LocaleInterface::class);
        $newLocale->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($newLocale);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'en_US'])
            ->willReturn(null);

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($newLocale, $result);
    }

    public function testReturnsExistingLocaleDirectlyWhenAlreadyHasId(): void
    {
        // Given
        $model = new class implements ModelInterface {
            public string $code = 'de_DE';
        };

        $existingLocale = $this->createMock(LocaleInterface::class);
        $existingLocale->method('getId')->willReturn(456);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($existingLocale);

        $this->repository
            ->expects($this->never())
            ->method('findOneBy');

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($existingLocale, $result);
    }

    public function testReturnsResourceWhenModelHasNoCodeProperty(): void
    {
        // Given
        $model = new class implements ModelInterface {
            // No code property
        };

        $locale = $this->createMock(LocaleInterface::class);
        $locale->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('getResource')
            ->with($model)
            ->willReturn($locale);

        $this->repository
            ->expects($this->never())
            ->method('findOneBy');

        // When
        $result = $this->provider->getResource($model);

        // Then
        $this->assertSame($locale, $result);
    }
}

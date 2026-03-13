<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Model;

use ACSEO\PrestashopMigrationPlugin\Model\LocaleFetcher;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class LocaleFetcherTest extends TestCase
{
    private EntityRepositoryInterface $entityRepository;
    private RepositoryInterface $resourceRepository;
    private LocaleFetcher $localeFetcher;

    protected function setUp(): void
    {
        $this->entityRepository = $this->createMock(EntityRepositoryInterface::class);
        $this->resourceRepository = $this->createMock(RepositoryInterface::class);
        $this->localeFetcher = new LocaleFetcher($this->entityRepository, $this->resourceRepository);
    }

    public function testGetLocaleReturnsLocaleForValidLanguageId(): void
    {
        // Given
        $languageId = 1;
        $language = ['id_lang' => 1, 'locale' => 'fr_FR'];

        $locale = $this->createMock(LocaleInterface::class);
        $locale->method('getCode')->willReturn('fr-fr');

        $this->entityRepository
            ->expects($this->once())
            ->method('find')
            ->with($languageId)
            ->willReturn($language);

        $this->resourceRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'fr_FR'])
            ->willReturn($locale);

        // When
        $result = $this->localeFetcher->getLocale($languageId);

        // Then
        $this->assertSame($locale, $result);
    }

    public function testGetLocaleReturnsCachedLocaleOnSecondCall(): void
    {
        // Given
        $languageId = 2;
        $language = ['id_lang' => 2, 'locale' => 'en_US'];

        $locale = $this->createMock(LocaleInterface::class);

        $this->entityRepository
            ->expects($this->once()) // Should only be called once
            ->method('find')
            ->with($languageId)
            ->willReturn($language);

        $this->resourceRepository
            ->expects($this->once()) // Should only be called once
            ->method('findOneBy')
            ->with(['code' => 'en_US'])
            ->willReturn($locale);

        // When
        $firstResult = $this->localeFetcher->getLocale($languageId);
        $secondResult = $this->localeFetcher->getLocale($languageId);

        // Then
        $this->assertSame($locale, $firstResult);
        $this->assertSame($locale, $secondResult);
        $this->assertSame($firstResult, $secondResult);
    }

    public function testGetLocaleThrowsExceptionWhenLanguageNotFound(): void
    {
        // Given
        $languageId = 999;

        $this->entityRepository
            ->expects($this->once())
            ->method('find')
            ->with($languageId)
            ->willReturn([]);

        // Expect
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lang 999 does not exist.');

        // When
        $this->localeFetcher->getLocale($languageId);
    }

    public function testGetLocaleThrowsExceptionWhenLocaleKeyMissing(): void
    {
        // Given
        $languageId = 3;
        $language = ['id_lang' => 3]; // Missing 'locale' key

        $this->entityRepository
            ->expects($this->once())
            ->method('find')
            ->with($languageId)
            ->willReturn($language);

        // Expect
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lang 3 does not exist.');

        // When
        $this->localeFetcher->getLocale($languageId);
    }

    public function testGetLocaleCodeReturnsCodeWhenLocaleExists(): void
    {
        // Given
        $languageId = 1;
        $language = ['id_lang' => 1, 'locale' => 'de_DE'];

        $locale = $this->createMock(LocaleInterface::class);
        $locale->method('getCode')->willReturn('de-de');

        $this->entityRepository
            ->expects($this->once())
            ->method('find')
            ->with($languageId)
            ->willReturn($language);

        $this->resourceRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'de_DE'])
            ->willReturn($locale);

        // When
        $result = $this->localeFetcher->getLocaleCode($languageId);

        // Then
        $this->assertSame('de-de', $result);
    }

    public function testGetLocaleCodeReturnsNullWhenLocaleNotFound(): void
    {
        // Given
        $languageId = 5;
        $language = ['id_lang' => 5, 'locale' => 'es_ES'];

        $this->entityRepository
            ->expects($this->once())
            ->method('find')
            ->with($languageId)
            ->willReturn($language);

        $this->resourceRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'es_ES'])
            ->willReturn(null);

        // When
        $result = $this->localeFetcher->getLocaleCode($languageId);

        // Then
        $this->assertNull($result);
    }

    public function testGetLocalesReturnsAllLocales(): void
    {
        // Given
        $languages = [
            ['id_lang' => 1, 'locale' => 'fr_FR'],
            ['id_lang' => 2, 'locale' => 'en_US'],
        ];

        $locale1 = $this->createMock(LocaleInterface::class);
        $locale2 = $this->createMock(LocaleInterface::class);

        $this->entityRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($languages);

        $this->entityRepository
            ->expects($this->exactly(2))
            ->method('find')
            ->willReturnCallback(function ($id) use ($languages) {
                return $languages[$id - 1];
            });

        $this->resourceRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls($locale1, $locale2);

        // When
        $result = $this->localeFetcher->getLocales();

        // Then
        $this->assertCount(2, $result);
        $this->assertContains($locale1, $result);
        $this->assertContains($locale2, $result);
    }

    public function testGetLocalesFiltersOutNullLocales(): void
    {
        // Given
        $languages = [
            ['id_lang' => 1, 'locale' => 'fr_FR'],
            ['id_lang' => 2, 'locale' => 'en_US'],
        ];

        $locale1 = $this->createMock(LocaleInterface::class);

        $this->entityRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($languages);

        $this->entityRepository
            ->expects($this->exactly(2))
            ->method('find')
            ->willReturnCallback(function ($id) use ($languages) {
                return $languages[$id - 1];
            });

        $this->resourceRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls($locale1, null);

        // When
        $result = $this->localeFetcher->getLocales();

        // Then
        $this->assertCount(1, $result);
        $this->assertContains($locale1, $result);
    }
}

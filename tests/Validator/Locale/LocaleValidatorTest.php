<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Validator\Locale;

use ACSEO\PrestashopMigrationPlugin\Validator\Locale\LocaleValidator;
use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Locale\Model\LocaleInterface;

class LocaleValidatorTest extends TestCase
{
    private ValidatorInterface $decorated;
    private LocaleValidator $validator;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ValidatorInterface::class);
        $this->validator = new LocaleValidator($this->decorated);
    }

    public function testSkipsValidationForExistingEntities(): void
    {
        // Given
        $locale = $this->createMock(LocaleInterface::class);
        $locale->method('getId')->willReturn(123);

        $this->decorated
            ->expects($this->never())
            ->method('validate');

        // When
        $result = $this->validator->validate($locale);

        // Then
        $this->assertTrue($result);
    }

    public function testDelegatesValidationForNewEntities(): void
    {
        // Given
        $locale = $this->createMock(LocaleInterface::class);
        $locale->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('validate')
            ->with($locale)
            ->willReturn(true);

        // When
        $result = $this->validator->validate($locale);

        // Then
        $this->assertTrue($result);
    }

    public function testReturnsDecoratedValidatorResultWhenValidationFails(): void
    {
        // Given
        $locale = $this->createMock(LocaleInterface::class);
        $locale->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('validate')
            ->with($locale)
            ->willReturn(false);

        // When
        $result = $this->validator->validate($locale);

        // Then
        $this->assertFalse($result);
    }
}

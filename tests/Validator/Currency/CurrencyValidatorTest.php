<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Validator\Currency;

use ACSEO\PrestashopMigrationPlugin\Validator\Currency\CurrencyValidator;
use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Currency\Model\CurrencyInterface;

class CurrencyValidatorTest extends TestCase
{
    private ValidatorInterface $decorated;
    private CurrencyValidator $validator;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ValidatorInterface::class);
        $this->validator = new CurrencyValidator($this->decorated);
    }

    public function testSkipsValidationForExistingEntities(): void
    {
        // Given
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getId')->willReturn(1);

        $this->decorated
            ->expects($this->never())
            ->method('validate');

        // When
        $result = $this->validator->validate($currency);

        // Then
        $this->assertTrue($result);
    }

    public function testDelegatesValidationForNewEntities(): void
    {
        // Given
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('validate')
            ->with($currency)
            ->willReturn(true);

        // When
        $result = $this->validator->validate($currency);

        // Then
        $this->assertTrue($result);
    }

    public function testReturnsDecoratedValidatorResultWhenValidationFails(): void
    {
        // Given
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('validate')
            ->with($currency)
            ->willReturn(false);

        // When
        $result = $this->validator->validate($currency);

        // Then
        $this->assertFalse($result);
    }
}

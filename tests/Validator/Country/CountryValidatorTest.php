<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Validator\Country;

use ACSEO\PrestashopMigrationPlugin\Validator\Country\CountryValidator;
use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Model\CountryInterface;

class CountryValidatorTest extends TestCase
{
    private ValidatorInterface $decorated;
    private CountryValidator $validator;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ValidatorInterface::class);
        $this->validator = new CountryValidator($this->decorated);
    }

    public function testSkipsValidationForExistingEntities(): void
    {
        // Given
        $country = $this->createMock(CountryInterface::class);
        $country->method('getId')->willReturn(1);
        $country->method('isEnabled')->willReturn(true);

        $this->decorated
            ->expects($this->never())
            ->method('validate');

        // When
        $result = $this->validator->validate($country);

        // Then
        $this->assertTrue($result);
    }

    public function testDelegatesValidationForNewEntities(): void
    {
        // Given
        $country = $this->createMock(CountryInterface::class);
        $country->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('validate')
            ->with($country)
            ->willReturn(true);

        // When
        $result = $this->validator->validate($country);

        // Then
        $this->assertTrue($result);
    }

    public function testReturnsDecoratedValidatorResultWhenValidationFails(): void
    {
        // Given
        $country = $this->createMock(CountryInterface::class);
        $country->method('getId')->willReturn(null);

        $this->decorated
            ->expects($this->once())
            ->method('validate')
            ->with($country)
            ->willReturn(false);

        // When
        $result = $this->validator->validate($country);

        // Then
        $this->assertFalse($result);
    }
}

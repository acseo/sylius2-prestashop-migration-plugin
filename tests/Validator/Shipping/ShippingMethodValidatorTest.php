<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Validator\Shipping;

use ACSEO\PrestashopMigrationPlugin\Validator\Shipping\ShippingMethodValidator;
use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Model\ResourceInterface;

class ShippingMethodValidatorTest extends TestCase
{
    private ValidatorInterface $decorated;
    private ShippingMethodValidator $validator;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ValidatorInterface::class);
        $this->validator = new ShippingMethodValidator($this->decorated);
    }

    public function testDelegatesValidationToDecorated(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->decorated
            ->expects(self::once())
            ->method('validate')
            ->with($resource)
            ->willReturn(true);

        $result = $this->validator->validate($resource);

        self::assertTrue($result);
    }

    public function testReturnsFalseWhenDecoratedFails(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->decorated
            ->expects(self::once())
            ->method('validate')
            ->with($resource)
            ->willReturn(false);

        $result = $this->validator->validate($resource);

        self::assertFalse($result);
    }
}

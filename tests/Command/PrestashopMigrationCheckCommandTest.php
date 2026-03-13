<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Command;

use ACSEO\PrestashopMigrationPlugin\Command\PrestashopMigrationCheckCommand;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PrestashopMigrationCheckCommandTest extends TestCase
{
    public function testEntityMappingContainsShippingMethod(): void
    {
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $command = new PrestashopMigrationCheckCommand($entityManager, []);

        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty('entityMapping');
        $property->setAccessible(true);
        $mapping = $property->getValue($command);

        self::assertArrayHasKey('shipping_method', $mapping);
        self::assertSame('Shipping Methods', $mapping['shipping_method']['label']);
        self::assertSame('prestashop:migration:shipping_method', $mapping['shipping_method']['command']);
        self::assertSame('App\Entity\Shipping\ShippingMethod', $mapping['shipping_method']['sylius_entity']);
    }
}

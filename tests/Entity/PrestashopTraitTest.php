<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Entity;

use ACSEO\PrestashopMigrationPlugin\Entity\PrestashopTrait;
use PHPUnit\Framework\TestCase;

class PrestashopTraitTest extends TestCase
{
    private object $entity;

    protected function setUp(): void
    {
        $this->entity = new class {
            use PrestashopTrait;
        };
    }

    public function testPrestashopIdGetterAndSetter(): void
    {
        // Given
        $prestashopId = 12345;

        // When
        $this->entity->setPrestashopId($prestashopId);

        // Then
        $this->assertSame($prestashopId, $this->entity->getPrestashopId());
    }

    public function testPrestashopIdDefaultsToNull(): void
    {
        // Then
        $this->assertNull($this->entity->getPrestashopId());
    }

    public function testPrestashopCreatedAtGetterAndSetter(): void
    {
        // Given
        $createdAt = new \DateTime('2024-01-15 10:30:00');

        // When
        $this->entity->setPrestashopCreatedAt($createdAt);

        // Then
        $this->assertSame($createdAt, $this->entity->getPrestashopCreatedAt());
    }

    public function testPrestashopCreatedAtDefaultsToNull(): void
    {
        // Then
        $this->assertNull($this->entity->getPrestashopCreatedAt());
    }

    public function testPrestashopUpdatedAtGetterAndSetter(): void
    {
        // Given
        $updatedAt = new \DateTime('2024-01-20 15:45:00');

        // When
        $this->entity->setPrestashopUpdatedAt($updatedAt);

        // Then
        $this->assertSame($updatedAt, $this->entity->getPrestashopUpdatedAt());
    }

    public function testPrestashopUpdatedAtDefaultsToNull(): void
    {
        // Then
        $this->assertNull($this->entity->getPrestashopUpdatedAt());
    }

    public function testAllPropertiesCanBeSetAndRetrieved(): void
    {
        // Given
        $prestashopId = 999;
        $createdAt = new \DateTime('2024-01-01 00:00:00');
        $updatedAt = new \DateTime('2024-01-31 23:59:59');

        // When
        $this->entity->setPrestashopId($prestashopId);
        $this->entity->setPrestashopCreatedAt($createdAt);
        $this->entity->setPrestashopUpdatedAt($updatedAt);

        // Then
        $this->assertSame($prestashopId, $this->entity->getPrestashopId());
        $this->assertSame($createdAt, $this->entity->getPrestashopCreatedAt());
        $this->assertSame($updatedAt, $this->entity->getPrestashopUpdatedAt());
    }
}

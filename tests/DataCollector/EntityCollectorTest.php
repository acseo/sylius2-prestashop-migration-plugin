<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\DataCollector;

use ACSEO\PrestashopMigrationPlugin\DataCollector\EntityCollector;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;
use PHPUnit\Framework\TestCase;

class EntityCollectorTest extends TestCase
{
    private EntityRepositoryInterface $repository;
    private EntityCollector $collector;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EntityRepositoryInterface::class);
        $this->collector = new EntityCollector($this->repository);
    }

    public function testCollectReturnsDataFromRepository(): void
    {
        // Given
        $limit = 10;
        $offset = 0;
        $expectedData = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->with($limit, $offset)
            ->willReturn($expectedData);

        // When
        $result = $this->collector->collect($limit, $offset);

        // Then
        $this->assertSame($expectedData, $result);
    }

    public function testCollectWithDifferentLimitAndOffset(): void
    {
        // Given
        $limit = 50;
        $offset = 100;
        $expectedData = [
            ['id' => 101, 'name' => 'Item 101'],
            ['id' => 102, 'name' => 'Item 102'],
        ];

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->with($limit, $offset)
            ->willReturn($expectedData);

        // When
        $result = $this->collector->collect($limit, $offset);

        // Then
        $this->assertSame($expectedData, $result);
    }

    public function testSizeReturnsCountFromRepository(): void
    {
        // Given
        $expectedCount = 250;

        $this->repository
            ->expects($this->once())
            ->method('count')
            ->willReturn($expectedCount);

        // When
        $result = $this->collector->size();

        // Then
        $this->assertSame($expectedCount, $result);
    }

    public function testSizeReturnsZeroWhenNoData(): void
    {
        // Given
        $this->repository
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        // When
        $result = $this->collector->size();

        // Then
        $this->assertSame(0, $result);
    }
}

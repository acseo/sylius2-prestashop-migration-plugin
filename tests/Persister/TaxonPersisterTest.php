<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Persister;

use ACSEO\PrestashopMigrationPlugin\Persister\PersisterInterface;
use ACSEO\PrestashopMigrationPlugin\Persister\Taxon\TaxonPersister;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TaxonPersisterTest extends TestCase
{
    private PersisterInterface $decoratedPersister;
    private EntityManagerInterface $entityManager;
    private TaxonPersister $persister;

    protected function setUp(): void
    {
        $this->decoratedPersister = $this->createMock(PersisterInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->persister = new TaxonPersister($this->decoratedPersister, $this->entityManager);
    }

    public function testPersistCallsDecoratedPersister(): void
    {
        // Given
        $data = ['id' => 1, 'name' => 'Category'];
        $dryRun = false;

        $this->decoratedPersister
            ->expects($this->once())
            ->method('persist')
            ->with($data, $dryRun);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // When
        $this->persister->persist($data, $dryRun);
    }

    public function testPersistDoesNotFlushInDryRunMode(): void
    {
        // Given
        $data = ['id' => 2, 'name' => 'Test Category'];
        $dryRun = true;

        $this->decoratedPersister
            ->expects($this->once())
            ->method('persist')
            ->with($data, $dryRun);

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        // When
        $this->persister->persist($data, $dryRun);
    }

    public function testPersistFlushesAfterDecoratedPersister(): void
    {
        // Given
        $data = ['id' => 3, 'name' => 'Another Category'];
        $dryRun = false;

        $callOrder = [];

        $this->decoratedPersister
            ->expects($this->once())
            ->method('persist')
            ->with($data, $dryRun)
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'persist';
            });

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'flush';
            });

        // When
        $this->persister->persist($data, $dryRun);

        // Then
        $this->assertSame(['persist', 'flush'], $callOrder);
    }
}

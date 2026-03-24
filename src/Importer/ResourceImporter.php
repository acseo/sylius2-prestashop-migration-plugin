<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Importer;

use Doctrine\ORM\EntityManagerInterface;
use ACSEO\PrestashopMigrationPlugin\DataCollector\DataCollectorInterface;

use ACSEO\PrestashopMigrationPlugin\Persister\PersisterInterface;
use ACSEO\PrestashopMigrationPlugin\Persister\PersistStatus;
use ACSEO\PrestashopMigrationPlugin\Validator\ViolationBagInterface;

class ResourceImporter implements ImporterInterface
{
    private string $name;

    private int $step;

    private DataCollectorInterface $collector;

    private PersisterInterface $persister;

    private EntityManagerInterface $entityManager;

    private ViolationBagInterface $violationBag;

    public function __construct(
        string                 $name,
        int                    $step,
        DataCollectorInterface $collector,
        PersisterInterface     $persister,
        EntityManagerInterface $entityManager,
        ViolationBagInterface $violationBag
    )
    {
        $this->name = $name;
        $this->step = $step;
        $this->collector = $collector;
        $this->persister = $persister;
        $this->entityManager = $entityManager;
        $this->violationBag = $violationBag;
    }

    public function import(?callable $callable = null, bool $dryRun = false): void
    {
        $offset = 0;

        while ($offset < $this->size()) {

            $collection = $this->collector->collect($this->step, $offset);
            $processed = count($collection);

            // Collect statuses for each persisted item
            $statuses = [];
            foreach ($collection as $item) {
                $status = $this->persister->persist($item, $dryRun);
                $statuses[] = $status;
            }

            if (!$dryRun) {
                $this->entityManager->flush();
            }

            $offset += $processed;

            if (null !== $callable) {
                $callable($processed, $statuses, $this->violationBag->all(), $dryRun);
            }
        }
    }

    public function size(): int
    {
        return $this->collector->size();
    }

    public function getName(): string
    {
        return $this->name;
    }
}

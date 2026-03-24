<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Persister\Taxon;

use Doctrine\ORM\EntityManagerInterface;
use ACSEO\PrestashopMigrationPlugin\Persister\PersisterInterface;
use ACSEO\PrestashopMigrationPlugin\Persister\PersistStatus;

class TaxonPersister implements PersisterInterface
{
    private PersisterInterface $persister;

    private EntityManagerInterface $manager;

    public function __construct(PersisterInterface $persister, EntityManagerInterface $manager)
    {
        $this->persister = $persister;
        $this->manager = $manager;
    }

    public function persist(array $data, bool $dryRun = false): PersistStatus
    {
        $status = $this->persister->persist($data, $dryRun);

        if (!$dryRun) {
            $this->manager->flush();
        }

        return $status;
    }
}

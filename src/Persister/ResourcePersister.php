<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Persister;

use Doctrine\ORM\EntityManagerInterface;
use ACSEO\PrestashopMigrationPlugin\DataTransformer\TransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;

class ResourcePersister implements PersisterInterface
{
    private EntityManagerInterface $manager;

    private TransformerInterface $transformer;

    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $manager, TransformerInterface $transformer, ValidatorInterface $validator)
    {
        $this->manager = $manager;
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    public function persist(array $data, bool $dryRun = false): PersistStatus
    {
        $resource = $this->transformer->transform($data);

        // Detect if this is a new entity or an update
        $isNewEntity = !method_exists($resource, 'getId') || null === $resource->getId();

        // Check if we should skip this entity before filling metadata
        if ($this->shouldSkipUpdate($resource, $data)) {
            return PersistStatus::SKIPPED;
        }

        // Automatically fill PrestaShop metadata
        $this->fillPrestashopMetadata($resource, $data);

        if ($this->validator->validate($resource)) {
            if (!$dryRun) {
                $this->manager->persist($resource);
            }
            return $isNewEntity ? PersistStatus::CREATED : PersistStatus::UPDATED;
        }

        return PersistStatus::FAILED;
    }

    /**
     * Determines if we should skip the update based on prestashop_updated_at
     *
     * Logic:
     * - If $resource doesn't have prestashop_updated_at yet → don't skip (new entity)
     * - If $data doesn't contain date_upd → don't skip (update for safety)
     * - Compare dates:
     *   - If PrestaShop date < Sylius date → skip (entity was manually modified in Sylius)
     *   - Otherwise → don't skip (normal update)
     */
    private function shouldSkipUpdate(object $resource, array $data): bool
    {
        // Check that the resource has the PrestashopTrait
        if (!method_exists($resource, 'getPrestashopUpdatedAt')) {
            return false;
        }

        // Get the current date from the entity in database
        $currentUpdatedAt = $resource->getPrestashopUpdatedAt();

        // If the entity has no date, it's a new entity
        if (null === $currentUpdatedAt) {
            return false;
        }

        // If PrestaShop has no date_upd, we update for safety
        if (!isset($data['date_upd']) || null === $data['date_upd']) {
            return false;
        }

        // Convert PrestaShop date
        try {
            $prestashopUpdatedAt = is_string($data['date_upd'])
                ? new \DateTime($data['date_upd'])
                : $data['date_upd'];
        } catch (\Exception $e) {
            // If conversion fails, we update for safety
            return false;
        }

        // Compare dates: skip if PrestaShop is older or equal to Sylius
        // (this means the entity was manually modified in Sylius after the last sync, or hasn't changed)
        return $prestashopUpdatedAt <= $currentUpdatedAt;
    }

    /**
     * Automatically fills prestashop_created_at and prestashop_updated_at fields
     * from PrestaShop source data (date_add and date_upd)
     */
    private function fillPrestashopMetadata(object $resource, array $data): void
    {
        // Check that the resource has the PrestashopTrait
        if (!method_exists($resource, 'setPrestashopCreatedAt')) {
            return;
        }

        // Fill prestashop_created_at from date_add
        if (isset($data['date_add']) && null !== $data['date_add']) {
            try {
                $createdAt = is_string($data['date_add'])
                    ? new \DateTime($data['date_add'])
                    : $data['date_add'];
                $resource->setPrestashopCreatedAt($createdAt);
            } catch (\Exception $e) {
                // If conversion fails, we silently ignore
            }
        }

        // Fill prestashop_updated_at from date_upd
        if (isset($data['date_upd']) && null !== $data['date_upd']) {
            try {
                $updatedAt = is_string($data['date_upd'])
                    ? new \DateTime($data['date_upd'])
                    : $data['date_upd'];
                $resource->setPrestashopUpdatedAt($updatedAt);
            } catch (\Exception $e) {
                // If conversion fails, we silently ignore
            }
        }
    }

}

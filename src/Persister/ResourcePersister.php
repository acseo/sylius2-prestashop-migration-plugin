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

    public function persist(array $data, bool $dryRun = false): void
    {
        $resource = $this->transformer->transform($data);
        echo ".";
        
        if ($this->validator->validate($resource)) {
            if (!$dryRun) {
                $this->manager->persist($resource);
            }
        } else {
            $errors = $this->validator->validate($resource);
            dump($errors);
            dump("invalid ressource : ");
            dump($resource);
            die();
        }
    }

}

<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model\Tax;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\TranslationModelTrait;

class TaxModel implements ModelInterface
{
    use TranslationModelTrait;

    #[Field(source: 'id_tax', target: 'prestashopId', id: true)]
    public int $id;

    #[Field(source: 'name', translatable: true)]
    public array $name;

    #[Field(source: 'rate', target: 'amount')]
    public float $rate;

    public function getName(string $locale): ?string
    {
        return $this->getTranslation($this->name, $locale);
    }

    public function hasName(string $locale): bool
    {
        return null !== $this->getName($locale);
    }

}

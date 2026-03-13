<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Model;

use ACSEO\PrestashopMigrationPlugin\Attribute\Field;

trait NameModelTrait
{
    use TranslationModelTrait;

    #[Field(source: 'name', target: 'name', translatable: true)]
    public array $name;

    public function getName(string $locale): ?string
    {
        return $this->getTranslation($this->name, $locale);
    }
}

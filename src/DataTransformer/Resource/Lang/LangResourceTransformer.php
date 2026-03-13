<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Lang;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class LangResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;

    public function __construct(ResourceTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function transform(ModelInterface $model): ResourceInterface
    {
        /**
         * @var LocaleInterface $locale
         */
        $locale = $this->transformer->transform($model);
        $locale->setCode(StringInflector::nameToCode($locale->getCode()));

        return $locale;
    }

}

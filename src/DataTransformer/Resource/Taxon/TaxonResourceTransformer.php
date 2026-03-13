<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Taxon;

use App\Entity\Taxonomy\Taxon;
use Behat\Transliterator\Transliterator;
use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Category\CategoryModel;
use ACSEO\PrestashopMigrationPlugin\Model\LocaleFetcher;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Taxonomy\Generator\TaxonSlugGeneratorInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;


final class TaxonResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;

    private TaxonSlugGeneratorInterface $taxonSlugGenerator;

    private TaxonRepositoryInterface $taxonRepository;

    private LocaleFetcher $localeFetcher;

    public function __construct(
        ResourceTransformerInterface $transformer,
        TaxonSlugGeneratorInterface  $taxonSlugGenerator,
        TaxonRepositoryInterface     $taxonRepository,
        LocaleFetcher                $localeFetcher
    )
    {
        $this->transformer = $transformer;
        $this->taxonSlugGenerator = $taxonSlugGenerator;
        $this->taxonRepository = $taxonRepository;
        $this->localeFetcher = $localeFetcher;
    }

    public function transform(ModelInterface $model): ResourceInterface
    {
        /**
         * @var Taxon $taxon
         */
        $taxon = $this->transformer->transform($model);

        $locales = $this->localeFetcher->getLocales();
        $fallbackLocaleCode = null;
        foreach ($locales as $candidateLocale) {
            $candidateCode = $candidateLocale->getCode();
            if (isset($model->name[$candidateCode]) && null !== $model->name[$candidateCode] && '' !== $model->name[$candidateCode]) {
                $fallbackLocaleCode = $candidateCode;
                break;
            }
        }

        foreach ($locales as $locale) {
            $taxon->setCurrentLocale($locale->getCode());
            if (null !== $fallbackLocaleCode) {
                $taxon->setFallbackLocale($fallbackLocaleCode);
            }

            $name = $model->name[$locale->getCode()] ?? ($fallbackLocaleCode ? ($model->name[$fallbackLocaleCode] ?? null) : null);
            if (null === $name || '' === $name) {
                continue;
            }

            $taxon->setName($name);
            $description = $model->description[$locale->getCode()] ?? ($fallbackLocaleCode ? ($model->description[$fallbackLocaleCode] ?? null) : null);
            if (null !== $description) {
                $taxon->setDescription($description);
            }

            //Set the name with code because prestashop can have multiple categories with same name. Can break the slug taxon in Sylius which is unique.
            if (null === $taxon->getId() && null === $taxon->getCode()) {
                $taxon->setCode(StringInflector::nameToLowercaseCode(Transliterator::transliterate(sprintf('%s %s', $taxon->getName(), $model->id))));
            }

            $taxon->setName($taxon->getCode());
            $slug = $this->taxonSlugGenerator->generate($taxon);
            $taxon->setName($name);

            $taxon->setSlug($slug);
        }

        $this->addParent($taxon, $model);

        return $taxon;
    }

    /**
     * @param TaxonInterface $taxon
     * @param CategoryModel $model
     *
     * @return void
     */
    private function addParent(TaxonInterface $taxon, ModelInterface $model): void
    {
        $parent = $this->taxonRepository->findOneBy(['prestashopId' => $model->parent]);

        if (null === $parent) {
            $parent = $this->taxonRepository->findOneBy(['code' => 'MENU_CATEGORY']);
        }

        $taxon->setParent($parent);
    }
}

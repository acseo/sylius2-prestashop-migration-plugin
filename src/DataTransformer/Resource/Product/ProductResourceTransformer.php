<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Product;

use Behat\Transliterator\Transliterator;
use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\DataTransformer\TransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\LocaleFetcher;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Product\ProductModel;
use ACSEO\PrestashopMigrationPlugin\Repository\Category\CategoryRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;
use ACSEO\PrestashopMigrationPlugin\Repository\Product\ProductAttributeRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Product\ProductRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Stock\StockAvailableRepository;
use ACSEO\PrestashopMigrationPlugin\Resolver\ConfigurationResolver;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Product\Generator\SlugGenerator;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ProductResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;

    /** @var ProductRepository $productRepository */
    private EntityRepositoryInterface $productRepository;

    /** @var ProductAttributeRepository $productAttributeRepository */
    private EntityRepositoryInterface $productAttributeRepository;

    /** @var StockAvailableRepository $stockAvailableRepository */
    private EntityRepositoryInterface $stockAvailableRepository;

    private RepositoryInterface $taxonRepository;

    private RepositoryInterface $channelRepository;

    private RepositoryInterface $productOptionValueRepository;

    private FactoryInterface $productTaxonFactory;

    private FactoryInterface $channelPricingFactory;

    private ProductVariantResolverInterface $defaultVariantResolver;

    private SlugGenerator $slugGenerator;

    private LocaleFetcher $localeFetcher;

    private ConfigurationResolver $configurationResolver;

    /** @var CategoryRepository $categoryRepository */
    private EntityRepositoryInterface $categoryRepository;

    private TransformerInterface $taxonTransformer;

    private EntityManagerInterface $entityManager;

    private RepositoryInterface $orderItemRepository;

    public function __construct(
        ResourceTransformerInterface    $transformer,
        EntityRepositoryInterface       $productRepository,
        EntityRepositoryInterface       $productAttributeRepository,
        EntityRepositoryInterface       $stockAvailableRepository,
        RepositoryInterface             $taxonRepository,
        RepositoryInterface             $channelRepository,
        RepositoryInterface             $productOptionValueRepository,
        FactoryInterface                $productTaxonFactory,
        FactoryInterface                $channelPricingFactory,
        ProductVariantResolverInterface $productVariantResolver,
        SlugGenerator                   $slugGenerator,
        LocaleFetcher                   $localeFetcher,
        ConfigurationResolver           $configurationResolver,
        EntityRepositoryInterface       $categoryRepository,
        TransformerInterface            $taxonTransformer,
        EntityManagerInterface          $entityManager,
        RepositoryInterface             $orderItemRepository,
    )
    {
        $this->transformer = $transformer;
        $this->productRepository = $productRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->stockAvailableRepository = $stockAvailableRepository;
        $this->taxonRepository = $taxonRepository;
        $this->channelRepository = $channelRepository;
        $this->productOptionValueRepository = $productOptionValueRepository;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->defaultVariantResolver = $productVariantResolver;
        $this->slugGenerator = $slugGenerator;
        $this->localeFetcher = $localeFetcher;
        $this->configurationResolver = $configurationResolver;
        $this->categoryRepository = $categoryRepository;
        $this->taxonTransformer = $taxonTransformer;
        $this->entityManager = $entityManager;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param ProductModel $model
     *
     * @return ResourceInterface
     * @throws \Exception
     */
    public function transform(ModelInterface $model): ResourceInterface
    {
        /**
         * @var ProductInterface $product
         */
        $product = $this->transformer->transform($model);

        $locales = $this->localeFetcher->getLocales();
        $fallbackLocaleCode = null;
        foreach ($locales as $candidateLocale) {
            $candidateCode = $candidateLocale->getCode();
            $candidateName = $model->getName($candidateCode);
            if (null !== $candidateName && '' !== $candidateName) {
                $fallbackLocaleCode = $candidateCode;
                break;
            }
        }

        foreach ($locales as $locale) {

            $product->setCurrentLocale($locale->getCode());
            if (null !== $fallbackLocaleCode) {
                $product->setFallbackLocale($fallbackLocaleCode);
            }

            $name = $model->getName($locale->getCode()) ?? ($fallbackLocaleCode ? $model->getName($fallbackLocaleCode) : null);
            if (null !== $name) {
                $product->setName($name);
            }

            $description = $model->getDescription($locale->getCode()) ?? ($fallbackLocaleCode ? $model->getDescription($fallbackLocaleCode) : null);
            if (null !== $description) {
                $product->setDescription($description);
            }

            if (null === $model->code && null === $product->getCode()) {
                $product->setCode($product->getName());
            }

            $this->addSlug($product, $model, $locale);
        }

        $this->addCode($product, $model);
        $this->addTaxons($product, $model);
        $this->addChannel($product, $model);
        $this->addOptions($product, $model);
        $this->addVariant($product);

        return $product;
    }

    private function addSlug(ProductInterface $product, ProductModel $model, LocaleInterface $locale): void
    {
        $slug = $model->getSlug($locale->getCode());
        if (null !== $slug) {
            $product->setSlug($slug);
        }

        $slugs = $this->productRepository->findBySlug($product->getSlug());

        if (count($slugs) > 1) {
            $product->setSlug($product->getSlug().'-'.$model->id);
        }

        $product->setSlug($this->slugGenerator->generate($product->getSlug()));
    }

    private function addCode(ProductInterface $product, ProductModel $model): void
    {
        //If code (or prestashop reference) is not unique, make sure it will be.
        $list = $this->productRepository->findByReference($product->getCode());

        if (count($list) > 1) {
            $product->setCode($product->getCode().'-'.$model->id);
        }

        $product->setCode(StringInflector::nameToLowercaseCode(Transliterator::transliterate($product->getCode())));
    }

    private function addTaxons(ProductInterface $product, ProductModel $model): void
    {
        $categories = $this->productRepository->getCategories($model->id);

        foreach ($categories as $category) {
            $categoryId = (int)$category['id_category'];

            /**
             * @var TaxonInterface|null $taxon
             */
            $taxon = $this->taxonRepository->findOneBy(['prestashopId' => $categoryId]);

            if (null === $taxon) {
                $taxon = $this->createMissingTaxon($categoryId);
            }

            if (null === $taxon || $product->hasTaxon($taxon)) {
                continue;
            }

            /**
             * @var ProductTaxonInterface $productTaxon
             */
            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setProduct($product);
            $productTaxon->setTaxon($taxon);
            $productTaxon->setPosition((int)$category['position']);

            if (!$product->hasProductTaxon($productTaxon)) {
                $product->addProductTaxon($productTaxon);
            }

            if ($model->categoryDefaultId === $categoryId) {
                $product->setMainTaxon($taxon);
            }
        }
    }

    private function createMissingTaxon(int $categoryId): ?TaxonInterface
    {
        // Root category is intentionally ignored by TaxonValidator
        if (1 === $categoryId) {
            return null;
        }

        $row = $this->categoryRepository->find($categoryId);
        if ([] === $row) {
            return null;
        }

        // Build the same "translatable" shape as EntityTranslatableCollector
        $translations = $this->categoryRepository->findTranslations($categoryId);
        foreach ($translations as $translation) {
            if (!array_key_exists('id_lang', $translation)) {
                continue;
            }

            $langId = (int) $translation['id_lang'];
            unset($translation['id_lang']);
            unset($translation['id_shop']);

            $diff = array_diff_assoc($translation, $row);
            foreach ($diff as $key => $value) {
                if (!array_key_exists($key, $row)) {
                    $row[$key] = [];
                }
                $row[$key][$langId] = $value;
            }
        }

        /** @var TaxonInterface $taxon */
        $taxon = $this->taxonTransformer->transform($row);

        $this->entityManager->persist($taxon);
        $this->entityManager->flush();

        return $taxon;
    }

    private function addVariant(ProductInterface $product): void
    {
        if ($product->getOptions()->isEmpty()) {
            $productVariant = $this->defaultVariantResolver->getVariant($product);

            $productVariant->setCode($product->getCode());
            $productVariant->setName($product->getName());

            $productVariant->setTracked($this->configurationResolver->hasStockEnabled());
            $productVariant->setOnHand($this->stockAvailableRepository->getQuantityByProductId($product->getPrestashopId()));
        }
    }

    private function addOptions(ProductInterface $product, ProductModel $model): void
    {
        foreach ($product->getOptions() as $option) {
            $product->removeOption($option);
        }

        $attributes = $this->productAttributeRepository->getAttributesByProductId($model->id);

        foreach ($attributes as $attribute) {
            $attributeId = $attribute['id_attribute'];

            /** @var ProductOptionValueInterface|null $productOptionValue */
            $productOptionValue = $this->productOptionValueRepository->findOneBy(['prestashopId' => $attributeId]);

            if ($productOptionValue && !$product->hasOption($productOptionValue->getOption())) {
                $product->addOption($productOptionValue->getOption());
            }
        }

        //If we have options, we destroy all variants to prevent future variation import.
        //Skip variants that are referenced by order items to avoid FK constraint violation.
        if ($product->hasOptions()) {
            foreach ($product->getVariants() as $variant) {
                if ($this->isVariantUsedInOrder($variant)) {
                    continue;
                }
                $product->removeVariant($variant);
            }

            //Choose match because Prestashop has no name for a variation.
            $product->setVariantSelectionMethod(ProductInterface::VARIANT_SELECTION_MATCH);
        }
    }

    private function isVariantUsedInOrder(ProductVariantInterface $variant): bool
    {
        $orderItem = $this->orderItemRepository->findOneBy(['variant' => $variant]);

        return null !== $orderItem;
    }

    private function addChannel(ProductInterface $product, ProductModel $model): void
    {
        $shops = $this->productRepository->getShops($model->id);

        foreach ($shops as $shop) {
            $shopId = (int)$shop['id_shop'];

            /**
             * @var ChannelInterface|null $channel
             */
            $channel = $this->channelRepository->findOneBy(['prestashopId' => $shopId]);

            if (null === $channel) {
                continue;
            }

            $product->addChannel($channel);

            //if product has no options, we need to create a default variation to set the price
            if (!$product->hasOptions()) {

                /**
                 * @var ProductVariantInterface $productVariant
                 */
                $productVariant = $this->defaultVariantResolver->getVariant($product);
                $channelPricing = $productVariant->getChannelPricingForChannel($channel);

                if (null === $channelPricing) {
                    /** @var ChannelPricingInterface $channelPricing */
                    $channelPricing = $this->channelPricingFactory->createNew();
                    $channelPricing->setChannelCode($channel->getCode());
                }

                $channelPricing->setPrice((int)$shop['price'] * 100);
                $productVariant->addChannelPricing($channelPricing);
            }
        }
    }
}

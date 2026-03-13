<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\Payment;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\LocaleFetcher;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Model\Payment\PaymentMethodModel;
use Behat\Transliterator\Transliterator;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class PaymentMethodResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $transformer;

    private FactoryInterface $gatewayConfigFactory;

    private LocaleFetcher $localeFetcher;

    private RepositoryInterface $channelRepository;

    private LoggerInterface $logger;

    public function __construct(
        ResourceTransformerInterface $transformer,
        FactoryInterface $gatewayConfigFactory,
        LocaleFetcher $localeFetcher,
        RepositoryInterface $channelRepository,
        LoggerInterface $logger
    ) {
        $this->transformer = $transformer;
        $this->gatewayConfigFactory = $gatewayConfigFactory;
        $this->localeFetcher = $localeFetcher;
        $this->channelRepository = $channelRepository;
        $this->logger = $logger;
    }

    public function transform(ModelInterface $model): ResourceInterface
    {
        /** @var PaymentMethodModel $model */
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->transformer->transform($model);

        // Generate unique code from module name
        if (null === $paymentMethod->getCode() || '' === $paymentMethod->getCode()) {
            $code = StringInflector::nameToLowercaseCode(
                Transliterator::transliterate($model->name)
            );
            $paymentMethod->setCode($code);
        }

        // Create or use existing GatewayConfig
        if (null === $paymentMethod->getGatewayConfig()) {
            $gatewayConfig = $this->createGatewayConfig($model->name);
            $paymentMethod->setGatewayConfig($gatewayConfig);
        }

        // Generate human-readable name from module name
        $displayName = $this->getDisplayName($model->name);

        // Set translations for all locales
        $locales = $this->localeFetcher->getLocales();
        foreach ($locales as $locale) {
            $translation = $paymentMethod->getTranslation($locale->getCode());
            $translation->setName($displayName);
            $translation->setDescription('Migrated from PrestaShop');
        }

        // Enable only if module is active in PrestaShop
        $paymentMethod->setEnabled($model->active === 1);

        // Associate with all channels
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            if ($channel instanceof ChannelInterface && !$paymentMethod->hasChannel($channel)) {
                $paymentMethod->addChannel($channel);
            }
        }

        $this->logger->info('PaymentMethod migrated from PrestaShop', [
            'code' => $paymentMethod->getCode(),
            'module' => $model->name,
            'name' => $displayName,
            'active' => $model->active,
            'currencies' => $model->currencies ?? 'all',
            'gateway' => $paymentMethod->getGatewayConfig()?->getFactoryName() ?? 'unknown',
        ]);

        return $paymentMethod;
    }

    /**
     * Create a GatewayConfig for the payment method.
     *
     * Maps known payment methods to specific gateways, defaults to offline.
     */
    private function createGatewayConfig(string $paymentMethodName): GatewayConfigInterface
    {
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $this->gatewayConfigFactory->createNew();

        // Map known payment methods to specific gateways, default to offline
        $factoryName = $this->mapToGatewayFactory($paymentMethodName);

        $gatewayConfig->setFactoryName($factoryName);
        $gatewayConfig->setGatewayName($factoryName === 'offline' ? 'Offline' : $paymentMethodName);
        $gatewayConfig->setConfig([]);

        return $gatewayConfig;
    }

    /**
     * Convert PrestaShop module name to human-readable display name.
     *
     * @param string $moduleName Technical module name (e.g., 'ps_wirepayment')
     * @return string Human-readable name (e.g., 'Wire Payment')
     */
    private function getDisplayName(string $moduleName): string
    {
        // Map known PrestaShop payment modules to display names
        $knownModules = [
            'ps_wirepayment' => 'Bank Wire Payment',
            'ps_checkpayment' => 'Payment by Check',
            'ps_cashondelivery' => 'Cash on Delivery',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'stripe_official' => 'Stripe',
            'mollie' => 'Mollie',
        ];

        if (isset($knownModules[$moduleName])) {
            return $knownModules[$moduleName];
        }

        // Generate name from module technical name
        // Remove common prefixes and convert to title case
        $name = preg_replace('/^(ps_|prestashop_)/', '', $moduleName);
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);

        return $name;
    }

    /**
     * Map PrestaShop module name to Sylius gateway factory.
     *
     * Maps known payment modules to their corresponding gateway factories.
     * All others default to offline.
     *
     * @param string $moduleName PrestaShop module name
     * @return string Sylius gateway factory name
     */
    private function mapToGatewayFactory(string $moduleName): string
    {
        $lowercaseName = strtolower($moduleName);

        // Map known payment modules to gateway factories
        if (str_contains($lowercaseName, 'paypal')) {
            return 'paypal';
        }

        if (str_contains($lowercaseName, 'stripe')) {
            return 'stripe';
        }

        // Default to offline for all others
        // Manual configuration will be needed for real gateways
        return 'offline';
    }
}

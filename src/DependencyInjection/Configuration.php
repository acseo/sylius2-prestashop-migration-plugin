<?php
declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\DependencyInjection;

use ACSEO\PrestashopMigrationPlugin\Model\Address\AddressModel;
use ACSEO\PrestashopMigrationPlugin\Model\Attribute\AttributeGroupModel;
use ACSEO\PrestashopMigrationPlugin\Model\Attribute\AttributeModel;
use ACSEO\PrestashopMigrationPlugin\Model\Category\CategoryModel;
use ACSEO\PrestashopMigrationPlugin\Model\Country\CountryModel;
use ACSEO\PrestashopMigrationPlugin\Model\Currency\CurrencyModel;
use ACSEO\PrestashopMigrationPlugin\Model\Customer\CustomerGroupModel;
use ACSEO\PrestashopMigrationPlugin\Model\Customer\CustomerModel;
use ACSEO\PrestashopMigrationPlugin\Model\Employee\EmployeeModel;
use ACSEO\PrestashopMigrationPlugin\Model\Lang\LangModel;
use ACSEO\PrestashopMigrationPlugin\Model\Product\ProductAttributeModel;
use ACSEO\PrestashopMigrationPlugin\Model\Order\OrderModel;
use ACSEO\PrestashopMigrationPlugin\Model\Product\ProductModel;
use ACSEO\PrestashopMigrationPlugin\Model\Shop\ShopModel;
use ACSEO\PrestashopMigrationPlugin\Model\Tax\TaxCategoryModel;
use ACSEO\PrestashopMigrationPlugin\Model\Tax\TaxModel;
use ACSEO\PrestashopMigrationPlugin\Model\Zone\ZoneModel;
use ACSEO\PrestashopMigrationPlugin\Repository\Address\AddressRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Category\CategoryRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Country\CountryRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Currency\CurrencyRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Customer\CustomerGroupRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Customer\CustomerRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Product\ProductAttributeRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Product\ProductRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Order\OrderRepository;
use ACSEO\PrestashopMigrationPlugin\Repository\Shop\ShopRepository;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('prestashop');

        $rootNode = $treeBuilder->getRootNode()->children();

        $rootNode
            ->scalarNode('connection')->defaultValue("")->info('Doctrine connection name')->cannotBeEmpty()->end()
            ->scalarNode('prefix')->defaultValue('ps_')->info('Table prefix for database')->cannotBeEmpty()->end()
            ->scalarNode('flush_step')->defaultValue(100)->info('Number of persist between flush during import.')->cannotBeEmpty()->end()
            ->scalarNode('public_directory')->defaultNull()->info('The public directory where the product images are stored (ex : "https://www.example.com/img/p/")')->cannotBeEmpty()->end()
            ->scalarNode('tmp_directory')->defaultValue('/tmp/prestashop')->info('The temporary directory where the product images will be downloaded.')->cannotBeEmpty()->end();

        $this->addOrderStateMappingSection($rootNode);
        $this->addResourceSection($rootNode);

        return $treeBuilder;
    }

    public function addOrderStateMappingSection(NodeBuilder $builder)
    {
        $defaultMapping = [
            1 => [  // Awaiting check payment
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'awaiting_payment',
                'shipping_state' => 'ready',
            ],
            2 => [  // Payment accepted
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'paid',
                'shipping_state' => 'ready',
            ],
            3 => [  // Processing in progress
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'paid',
                'shipping_state' => 'ready',
            ],
            4 => [  // Shipped
                'order_state' => 'fulfilled',
                'checkout_state' => 'completed',
                'payment_state' => 'paid',
                'shipping_state' => 'shipped',
            ],
            5 => [  // Delivered
                'order_state' => 'fulfilled',
                'checkout_state' => 'completed',
                'payment_state' => 'paid',
                'shipping_state' => 'shipped',
            ],
            6 => [  // Canceled
                'order_state' => 'cancelled',
                'checkout_state' => 'completed',
                'payment_state' => 'cancelled',
                'shipping_state' => 'cancelled',
            ],
            7 => [  // Refunded
                'order_state' => 'cancelled',
                'checkout_state' => 'completed',
                'payment_state' => 'refunded',
                'shipping_state' => 'cancelled',
            ],
            8 => [  // Payment error
                'order_state' => 'cancelled',
                'checkout_state' => 'completed',
                'payment_state' => 'cancelled',
                'shipping_state' => 'cancelled',
            ],
            9 => [  // On backorder (paid)
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'paid',
                'shipping_state' => 'ready',
            ],
            10 => [  // Awaiting bank wire payment
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'awaiting_payment',
                'shipping_state' => 'ready',
            ],
            11 => [  // Remote payment accepted
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'paid',
                'shipping_state' => 'ready',
            ],
            12 => [  // On backorder (not paid)
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'awaiting_payment',
                'shipping_state' => 'ready',
            ],
            13 => [  // Awaiting Cash On Delivery validation
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'awaiting_payment',
                'shipping_state' => 'ready',
            ],
            14 => [  // Waiting for payment
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'awaiting_payment',
                'shipping_state' => 'ready',
            ],
            15 => [  // Partial refund
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'partially_refunded',
                'shipping_state' => 'ready',
            ],
            16 => [  // Partial payment
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'partially_paid',
                'shipping_state' => 'ready',
            ],
            17 => [  // Authorized. To be captured by merchant
                'order_state' => 'new',
                'checkout_state' => 'completed',
                'payment_state' => 'authorized',
                'shipping_state' => 'ready',
            ],
        ];

        $builder
            ->arrayNode('order_state_mapping')
                ->defaultValue($defaultMapping)
                ->useAttributeAsKey('prestashop_state_id')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('order_state')->defaultValue('new')->end()
                        ->scalarNode('checkout_state')->defaultValue('completed')->end()
                        ->scalarNode('payment_state')->defaultValue('awaiting_payment')->end()
                        ->scalarNode('shipping_state')->defaultValue('ready')->end()
                    ->end()
                ->end()
            ->end();
    }

    public function addResourceSection(NodeBuilder $builder){
        $builder
            ->arrayNode('resources')
                ->children()
                    ->arrayNode('address')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('address')->end()
                            ->scalarNode('repository')->defaultValue(AddressRepository::class)->end()
                            ->scalarNode('model')->defaultValue(AddressModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_address')->end()
                            ->scalarNode('sylius')->defaultValue('address')->end()
                            ->scalarNode('priority')->defaultValue(220)->end()
                        ->end()
                    ->end()
                    ->arrayNode('admin_user')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('employee')->end()
                            ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                            ->scalarNode('model')->defaultValue(EmployeeModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_employee')->end()
                            ->scalarNode('sylius')->defaultValue('admin_user')->end()
                            ->scalarNode('priority')->defaultValue(240)->end()
                        ->end()
                    ->end()
                    ->arrayNode('channel')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('shop')->end()
                            ->scalarNode('repository')->defaultValue(ShopRepository::class)->end()
                            ->scalarNode('model')->defaultValue(ShopModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_shop')->end()
                            ->scalarNode('sylius')->defaultValue('channel')->end()
                            ->scalarNode('priority')->defaultValue(250)->end()
                        ->end()
                    ->end()
                    ->arrayNode('country')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('country')->end()
                            ->scalarNode('repository')->defaultValue(CountryRepository::class)->end()
                            ->scalarNode('model')->defaultValue(CountryModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_country')->end()
                            ->scalarNode('sylius')->defaultValue('country')->end()
                            ->scalarNode('priority')->defaultValue(255)->end()
                        ->end()
                    ->end()
                    ->arrayNode('currency')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('currency')->end()
                            ->scalarNode('repository')->defaultValue(CurrencyRepository::class)->end()
                            ->scalarNode('model')->defaultValue(CurrencyModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_currency')->end()
                            ->scalarNode('sylius')->defaultValue('currency')->end()
                            ->scalarNode('priority')->defaultValue(255)->end()
                        ->end()
                    ->end()
                    ->arrayNode('customer_group')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('group')->end()
                            ->scalarNode('repository')->defaultValue(CustomerGroupRepository::class)->end()
                            ->scalarNode('model')->defaultValue(CustomerGroupModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_group')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('customer_group')->end()
                            ->scalarNode('priority')->defaultValue(235)->end()
                        ->end()
                    ->end()
                    ->arrayNode('customer')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('customer')->end()
                            ->scalarNode('repository')->defaultValue(CustomerRepository::class)->end()
                            ->scalarNode('model')->defaultValue(CustomerModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_customer')->end()
                            ->scalarNode('sylius')->defaultValue('customer')->end()
                            ->scalarNode('priority')->defaultValue(230)->end()
                        ->end()
                    ->end()
                    ->arrayNode('locale')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('lang')->end()
                            ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                            ->scalarNode('model')->defaultValue(LangModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_lang')->end()
                            ->scalarNode('sylius')->defaultValue('locale')->end()
                            ->scalarNode('priority')->defaultValue(255)->end()
                        ->end()
                    ->end()
                    ->arrayNode('product')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('product')->end()
                            ->scalarNode('repository')->defaultValue(ProductRepository::class)->end()
                            ->scalarNode('model')->defaultValue(ProductModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_product')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('product')->end()
                            ->scalarNode('priority')->defaultValue(200)->end()
                        ->end()
                    ->end()
                    ->arrayNode('order')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('orders')->end()
                            ->scalarNode('repository')->defaultValue(OrderRepository::class)->end()
                            ->scalarNode('model')->defaultValue(OrderModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_order')->end()
                            ->scalarNode('use_translation')->defaultValue(false)->end()
                            ->scalarNode('sylius')->defaultValue('order')->end()
                            ->scalarNode('priority')->defaultValue(180)->end()
                        ->end()
                    ->end()
                    ->arrayNode('product_option')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('attribute_group')->end()
                            ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                            ->scalarNode('model')->defaultValue(AttributeGroupModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_attribute_group')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('product_option')->end()
                            ->scalarNode('priority')->defaultValue(210)->end()
                        ->end()
                    ->end()
                    ->arrayNode('product_option_value')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('attribute')->end()
                            ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                            ->scalarNode('model')->defaultValue(AttributeModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_attribute')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('product_option_value')->end()
                            ->scalarNode('priority')->defaultValue(205)->end()
                        ->end()
                    ->end()
                    ->arrayNode('product_variant')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('product_attribute')->end()
                            ->scalarNode('repository')->defaultValue(ProductAttributeRepository::class)->end()
                            ->scalarNode('model')->defaultValue(ProductAttributeModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_product_attribute')->end()
                            ->scalarNode('use_translation')->defaultValue(false)->end()
                            ->scalarNode('sylius')->defaultValue('product_variant')->end()
                            ->scalarNode('priority')->defaultValue(190)->end()
                        ->end()
                    ->end()
                    ->arrayNode('taxon')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('category')->end()
                            ->scalarNode('repository')->defaultValue(CategoryRepository::class)->end()
                            ->scalarNode('model')->defaultValue(CategoryModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_category')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('taxon')->end()
                            ->scalarNode('priority')->defaultValue(210)->end()
                        ->end()
                    ->end()
                   /* ->arrayNode('shipping_method')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('carrier')->end()
                            ->scalarNode('repository')->defaultValue(CarrierRepository::class)->end()
                            ->scalarNode('model')->defaultValue(CarrierModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_carrier')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('shipping_method')->end()
                            ->scalarNode('priority')->defaultValue(240)->end()
                        ->end()
                    ->end()*/
                    ->arrayNode('tax_category')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('tax')->end()
                            ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                            ->scalarNode('model')->defaultValue(TaxCategoryModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_tax')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('tax_category')->end()
                            ->scalarNode('priority')->defaultValue(255)->end()
                        ->end()
                    ->end()
                    ->arrayNode('tax_rate')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('tax')->end()
                            ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                            ->scalarNode('model')->defaultValue(TaxModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_tax')->end()
                            ->scalarNode('use_translation')->defaultValue(true)->end()
                            ->scalarNode('sylius')->defaultValue('tax_rate')->end()
                            ->scalarNode('priority')->defaultValue(245)->end()
                        ->end()
                    ->end()
                    ->arrayNode('zone')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('table')->defaultValue('zone')->end()
                            ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                            ->scalarNode('model')->defaultValue(ZoneModel::class)->end()
                            ->scalarNode('primary_key')->defaultValue('id_zone')->end()
                            ->scalarNode('use_translation')->defaultValue(false)->end()
                            ->scalarNode('sylius')->defaultValue('zone')->end()
                            ->scalarNode('priority')->defaultValue(250)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        }
}

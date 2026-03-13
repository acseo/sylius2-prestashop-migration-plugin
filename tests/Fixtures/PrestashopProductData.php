<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Fixtures;

/**
 * Fixtures contenant des données réelles de produits PrestaShop
 * Ces structures correspondent exactement à ce qui est retourné par les requêtes PrestaShop
 */
class PrestashopProductData
{
    /**
     * Exemple de produit simple PrestaShop
     */
    public static function getSimpleProduct(): array
    {
        return [
            'id_product' => 1,
            'id_supplier' => 1,
            'id_manufacturer' => 1,
            'id_category_default' => 4,
            'id_shop_default' => 1,
            'id_tax_rules_group' => 1,
            'reference' => 'demo_1',
            'supplier_reference' => '',
            'location' => '',
            'width' => '0.000000',
            'height' => '0.000000',
            'depth' => '0.000000',
            'weight' => '0.000000',
            'quantity_discount' => 0,
            'ean13' => '',
            'isbn' => '',
            'upc' => '',
            'mpn' => '',
            'cache_is_pack' => 0,
            'cache_has_attachment' => 0,
            'is_virtual' => 0,
            'state' => 1,
            'additional_delivery_times' => 1,
            'delivery_in_stock' => '',
            'delivery_out_stock' => '',
            'product_type' => 'standard',
            'on_sale' => 0,
            'online_only' => 0,
            'ecotax' => '0.000000',
            'minimal_quantity' => 1,
            'low_stock_threshold' => null,
            'low_stock_alert' => 0,
            'price' => '23.900000',
            'wholesale_price' => '0.000000',
            'unity' => '',
            'unit_price_ratio' => '0.000000',
            'additional_shipping_cost' => '0.000000',
            'customizable' => 0,
            'text_fields' => 0,
            'uploadable_files' => 0,
            'active' => 1,
            'redirect_type' => '404',
            'id_type_redirected' => 0,
            'available_for_order' => 1,
            'available_date' => '0000-00-00',
            'show_condition' => 0,
            'condition' => 'new',
            'show_price' => 1,
            'indexed' => 1,
            'visibility' => 'both',
            'cache_default_attribute' => 1,
            'advanced_stock_management' => 0,
            'date_add' => '2024-01-15 10:00:00',
            'date_upd' => '2024-01-20 15:30:00',
            'pack_stock_type' => 3,
            // Données traduites
            'name' => [
                1 => 'T-shirt imprimé colibri',
                2 => 'Hummingbird printed t-shirt',
            ],
            'description' => [
                1 => '<p>Le t-shirt à manches courtes pour homme est un classique intemporel.</p>',
                2 => '<p>The short-sleeved t-shirt for men is a timeless classic.</p>',
            ],
            'description_short' => [
                1 => '<p>T-shirt décontracté pour homme</p>',
                2 => '<p>Casual t-shirt for men</p>',
            ],
            'link_rewrite' => [
                1 => 't-shirt-imprime-colibri',
                2 => 'hummingbird-printed-t-shirt',
            ],
            'meta_description' => [
                1 => 'Découvrez notre t-shirt imprimé colibri',
                2 => 'Discover our hummingbird printed t-shirt',
            ],
            'meta_keywords' => [
                1 => 't-shirt, colibri, mode',
                2 => 't-shirt, hummingbird, fashion',
            ],
            'meta_title' => [
                1 => 'T-shirt imprimé colibri',
                2 => 'Hummingbird printed t-shirt',
            ],
            'available_now' => [
                1 => 'En stock',
                2 => 'In stock',
            ],
            'available_later' => [
                1 => 'Rupture de stock',
                2 => 'Out of stock',
            ],
        ];
    }

    /**
     * Exemple de produit avec variantes PrestaShop
     */
    public static function getProductWithVariants(): array
    {
        return [
            'id_product' => 2,
            'reference' => 'demo_2',
            'price' => '35.900000',
            'active' => 1,
            'name' => [
                1 => 'Pull brodé ours brun',
                2 => 'Brown bear embroidered sweater',
            ],
            'description' => [
                1 => '<p>Pull en maille douce avec broderie ours</p>',
                2 => '<p>Soft knit sweater with bear embroidery</p>',
            ],
            'description_short' => [
                1 => '<p>Pull confortable pour homme</p>',
                2 => '<p>Comfortable sweater for men</p>',
            ],
            'link_rewrite' => [
                1 => 'pull-brode-ours-brun',
                2 => 'brown-bear-embroidered-sweater',
            ],
            'date_add' => '2024-01-16 11:00:00',
            'date_upd' => '2024-01-21 16:00:00',
        ];
    }

    /**
     * Variantes d'un produit PrestaShop
     */
    public static function getProductVariants(): array
    {
        return [
            [
                'id_product_attribute' => 1,
                'id_product' => 2,
                'reference' => 'demo_2_S_white',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => 1,
                'minimal_quantity' => 1,
                'low_stock_threshold' => null,
                'low_stock_alert' => 0,
                'available_date' => '0000-00-00',
            ],
            [
                'id_product_attribute' => 2,
                'id_product' => 2,
                'reference' => 'demo_2_M_white',
                'price' => '0.000000',
                'default_on' => 0,
                'minimal_quantity' => 1,
            ],
            [
                'id_product_attribute' => 3,
                'id_product' => 2,
                'reference' => 'demo_2_L_black',
                'price' => '2.000000',
                'default_on' => 0,
                'minimal_quantity' => 1,
            ],
        ];
    }

    /**
     * Stock disponible pour les produits
     */
    public static function getStockAvailable(): array
    {
        return [
            [
                'id_stock_available' => 1,
                'id_product' => 1,
                'id_product_attribute' => 1,
                'id_shop' => 1,
                'id_shop_group' => 0,
                'quantity' => 300,
                'depends_on_stock' => 0,
                'out_of_stock' => 2,
            ],
            [
                'id_stock_available' => 2,
                'id_product' => 2,
                'id_product_attribute' => 1,
                'id_shop' => 1,
                'quantity' => 50,
                'depends_on_stock' => 0,
                'out_of_stock' => 2,
            ],
            [
                'id_stock_available' => 3,
                'id_product' => 2,
                'id_product_attribute' => 2,
                'quantity' => 30,
                'depends_on_stock' => 0,
                'out_of_stock' => 2,
            ],
        ];
    }

    /**
     * Catégories PrestaShop
     */
    public static function getCategories(): array
    {
        return [
            [
                'id_category' => 4,
                'id_parent' => 2,
                'level_depth' => 2,
                'active' => 1,
                'position' => 0,
                'is_root_category' => 0,
                'date_add' => '2024-01-10 10:00:00',
                'date_upd' => '2024-01-10 10:00:00',
                'name' => [
                    1 => 'Hommes',
                    2 => 'Men',
                ],
                'description' => [
                    1 => 'Articles pour hommes',
                    2 => 'Items for men',
                ],
                'link_rewrite' => [
                    1 => 'hommes',
                    2 => 'men',
                ],
            ],
        ];
    }

    /**
     * Langues PrestaShop
     */
    public static function getLanguages(): array
    {
        return [
            [
                'id_lang' => 1,
                'name' => 'Français (French)',
                'active' => 1,
                'iso_code' => 'fr',
                'language_code' => 'fr-fr',
                'locale' => 'fr_FR',
                'date_format_lite' => 'd/m/Y',
                'date_format_full' => 'd/m/Y H:i:s',
                'is_rtl' => 0,
            ],
            [
                'id_lang' => 2,
                'name' => 'English (English)',
                'active' => 1,
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'locale' => 'en_US',
                'date_format_lite' => 'm/d/Y',
                'date_format_full' => 'm/d/Y H:i:s',
                'is_rtl' => 0,
            ],
        ];
    }

    /**
     * Groupes d'attributs (Options de produit)
     */
    public static function getAttributeGroups(): array
    {
        return [
            [
                'id_attribute_group' => 1,
                'is_color_group' => 0,
                'group_type' => 'select',
                'position' => 0,
                'name' => [
                    1 => 'Taille',
                    2 => 'Size',
                ],
                'public_name' => [
                    1 => 'Taille',
                    2 => 'Size',
                ],
            ],
            [
                'id_attribute_group' => 2,
                'is_color_group' => 1,
                'group_type' => 'color',
                'position' => 1,
                'name' => [
                    1 => 'Couleur',
                    2 => 'Color',
                ],
                'public_name' => [
                    1 => 'Couleur',
                    2 => 'Color',
                ],
            ],
        ];
    }

    /**
     * Valeurs d'attributs
     */
    public static function getAttributes(): array
    {
        return [
            [
                'id_attribute' => 1,
                'id_attribute_group' => 1,
                'color' => '',
                'position' => 0,
                'name' => [
                    1 => 'S',
                    2 => 'S',
                ],
            ],
            [
                'id_attribute' => 2,
                'id_attribute_group' => 1,
                'color' => '',
                'position' => 1,
                'name' => [
                    1 => 'M',
                    2 => 'M',
                ],
            ],
            [
                'id_attribute' => 3,
                'id_attribute_group' => 1,
                'color' => '',
                'position' => 2,
                'name' => [
                    1 => 'L',
                    2 => 'L',
                ],
            ],
            [
                'id_attribute' => 4,
                'id_attribute_group' => 2,
                'color' => '#FFFFFF',
                'position' => 0,
                'name' => [
                    1 => 'Blanc',
                    2 => 'White',
                ],
            ],
            [
                'id_attribute' => 5,
                'id_attribute_group' => 2,
                'color' => '#000000',
                'position' => 1,
                'name' => [
                    1 => 'Noir',
                    2 => 'Black',
                ],
            ],
        ];
    }

    /**
     * Configuration PrestaShop
     */
    public static function getConfiguration(): array
    {
        return [
            [
                'id_configuration' => 1,
                'name' => 'PS_WEIGHT_UNIT',
                'value' => 'kg',
            ],
            [
                'id_configuration' => 2,
                'name' => 'PS_DIMENSION_UNIT',
                'value' => 'cm',
            ],
            [
                'id_configuration' => 3,
                'name' => 'PS_CURRENCY_DEFAULT',
                'value' => '1',
            ],
        ];
    }
}

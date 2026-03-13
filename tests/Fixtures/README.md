# Fixtures - Données de Test PrestaShop

Ce dossier contient des données de test réalistes basées sur la structure PrestaShop.

## Qu'est-ce qu'une Fixture ?

Une fixture est un ensemble de données de test qui représente exactement la structure des données dans votre base de données source (PrestaShop).

## Pourquoi utiliser des Fixtures ?

1. **Tests réalistes**: Les données correspondent à la vraie structure PrestaShop
2. **Réutilisabilité**: Une fixture peut être utilisée dans plusieurs tests
3. **Maintenance**: Modifier une fixture met à jour tous les tests qui l'utilisent
4. **Documentation**: Les fixtures documentent la structure attendue des données

## Créer vos propres Fixtures depuis votre base PrestaShop

### Étape 1: Extraire les données de PrestaShop

Connectez-vous à votre base de données PrestaShop et exportez des exemples de données:

```sql
-- Exporter un produit
SELECT * FROM ps_product WHERE id_product = 1;
SELECT * FROM ps_product_lang WHERE id_product = 1;
SELECT * FROM ps_product_attribute WHERE id_product = 1;
SELECT * FROM ps_stock_available WHERE id_product = 1;

-- Exporter un client
SELECT * FROM ps_customer WHERE id_customer = 1;
SELECT * FROM ps_address WHERE id_customer = 1;

-- Exporter une commande
SELECT * FROM ps_orders WHERE id_order = 1;
SELECT * FROM ps_order_detail WHERE id_order = 1;
```

### Étape 2: Convertir en PHP

Créez un fichier fixture (ex: `MyShopProductData.php`):

```php
<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Fixtures;

/**
 * Fixtures basées sur les données réelles de MA boutique PrestaShop
 */
class MyShopProductData
{
    /**
     * Produit avec toutes les options de ma boutique
     */
    public static function getMyComplexProduct(): array
    {
        return [
            // Collez ici les données de votre requête SQL
            'id_product' => 1,
            'reference' => 'REF-001',
            'price' => '149.990000',
            'active' => 1,
            'name' => [
                1 => 'Mon Produit en Français',
                2 => 'My Product in English',
            ],
            // ... tous les autres champs
        ];
    }

    /**
     * Produit avec cas particulier spécifique à ma boutique
     */
    public static function getProductWithCustomFields(): array
    {
        return [
            'id_product' => 2,
            // Cas spécifique: produit avec champ personnalisé
            'custom_field_1' => 'valeur_specifique',
            // ...
        ];
    }
}
```

### Étape 3: Script d'extraction automatique

Créez un script pour extraire automatiquement les données:

```php
<?php
// scripts/extract-fixtures.php

$pdo = new PDO('mysql:host=localhost;dbname=prestashop', 'user', 'password');

// Extraire un produit
$stmt = $pdo->prepare("
    SELECT p.*, pl.name, pl.description
    FROM ps_product p
    LEFT JOIN ps_product_lang pl ON p.id_product = pl.id_product
    WHERE p.id_product = ?
");
$stmt->execute([1]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Convertir en fixture PHP
echo "public static function getProduct(): array\n";
echo "{\n";
echo "    return " . var_export($product, true) . ";\n";
echo "}\n";
```

Exécutez:
```bash
php scripts/extract-fixtures.php > tests/Fixtures/ExtractedData.php
```

## Exemples de Fixtures par Entité

### Produits (`PrestashopProductData.php`)

```php
// Produit simple
PrestashopProductData::getSimpleProduct();

// Produit avec variantes
PrestashopProductData::getProductWithVariants();

// Variantes d'un produit
PrestashopProductData::getProductVariants();

// Stock disponible
PrestashopProductData::getStockAvailable();
```

### Clients

```php
<?php

class PrestashopCustomerData
{
    public static function getCustomer(): array
    {
        return [
            'id_customer' => 1,
            'id_shop_group' => 1,
            'id_shop' => 1,
            'id_gender' => 1,
            'id_default_group' => 3,
            'id_lang' => 1,
            'id_risk' => 0,
            'company' => '',
            'siret' => '',
            'ape' => '',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'passwd' => '$2y$10$...hashed...', // Hash BCrypt
            'last_passwd_gen' => '2024-01-15 10:00:00',
            'birthday' => '1990-05-15',
            'newsletter' => 1,
            'ip_registration_newsletter' => '',
            'newsletter_date_add' => '2024-01-15 10:00:00',
            'optin' => 1,
            'website' => '',
            'outstanding_allow_amount' => '0.000000',
            'show_public_prices' => 0,
            'max_payment_days' => 0,
            'secure_key' => 'abcdef123456',
            'note' => '',
            'active' => 1,
            'is_guest' => 0,
            'deleted' => 0,
            'date_add' => '2024-01-15 10:00:00',
            'date_upd' => '2024-01-20 15:30:00',
            'reset_password_token' => null,
            'reset_password_validity' => '0000-00-00 00:00:00',
        ];
    }

    public static function getAddress(): array
    {
        return [
            'id_address' => 1,
            'id_country' => 8, // France
            'id_state' => 0,
            'id_customer' => 1,
            'id_manufacturer' => 0,
            'id_supplier' => 0,
            'id_warehouse' => 0,
            'alias' => 'Mon adresse',
            'company' => '',
            'lastname' => 'Doe',
            'firstname' => 'John',
            'vat_number' => '',
            'address1' => '123 Rue de la Paix',
            'address2' => 'Appartement 4B',
            'postcode' => '75001',
            'city' => 'Paris',
            'other' => '',
            'phone' => '0123456789',
            'phone_mobile' => '0612345678',
            'dni' => '',
            'deleted' => 0,
            'date_add' => '2024-01-15 10:00:00',
            'date_upd' => '2024-01-15 10:00:00',
        ];
    }
}
```

### Commandes

```php
<?php

class PrestashopOrderData
{
    public static function getOrder(): array
    {
        return [
            'id_order' => 1,
            'reference' => 'XHXPSMNZA',
            'id_shop_group' => 1,
            'id_shop' => 1,
            'id_carrier' => 2,
            'id_lang' => 1,
            'id_customer' => 1,
            'id_cart' => 1,
            'id_currency' => 1,
            'id_address_delivery' => 1,
            'id_address_invoice' => 1,
            'current_state' => 2, // Payment accepted
            'secure_key' => 'abc123def456',
            'payment' => 'Credit Card',
            'module' => 'stripe',
            'recyclable' => 0,
            'gift' => 0,
            'gift_message' => '',
            'mobile_theme' => 0,
            'shipping_number' => '',
            'total_discounts' => '0.000000',
            'total_discounts_tax_incl' => '0.000000',
            'total_discounts_tax_excl' => '0.000000',
            'total_paid' => '51.800000',
            'total_paid_tax_incl' => '51.800000',
            'total_paid_tax_excl' => '43.166667',
            'total_paid_real' => '51.800000',
            'total_products' => '43.166667',
            'total_products_wt' => '51.800000',
            'total_shipping' => '0.000000',
            'total_shipping_tax_incl' => '0.000000',
            'total_shipping_tax_excl' => '0.000000',
            'carrier_tax_rate' => '0.000',
            'total_wrapping' => '0.000000',
            'total_wrapping_tax_incl' => '0.000000',
            'total_wrapping_tax_excl' => '0.000000',
            'round_mode' => 2,
            'round_type' => 1,
            'invoice_number' => 1,
            'delivery_number' => 1,
            'invoice_date' => '2024-01-15 10:30:00',
            'delivery_date' => '2024-01-18 14:00:00',
            'valid' => 1,
            'date_add' => '2024-01-15 10:00:00',
            'date_upd' => '2024-01-20 15:30:00',
        ];
    }

    public static function getOrderDetails(): array
    {
        return [
            [
                'id_order_detail' => 1,
                'id_order' => 1,
                'id_order_invoice' => 1,
                'id_warehouse' => 0,
                'id_shop' => 1,
                'product_id' => 1,
                'product_attribute_id' => 1,
                'id_customization' => 0,
                'product_name' => 'T-shirt imprimé colibri - Taille S, Couleur Blanc',
                'product_quantity' => 2,
                'product_quantity_in_stock' => 2,
                'product_quantity_refunded' => 0,
                'product_quantity_return' => 0,
                'product_quantity_reinjected' => 0,
                'product_price' => '23.900000',
                'reduction_percent' => '0.00',
                'reduction_amount' => '0.000000',
                'reduction_amount_tax_incl' => '0.000000',
                'reduction_amount_tax_excl' => '0.000000',
                'group_reduction' => '0.00',
                'product_quantity_discount' => '0.000000',
                'product_ean13' => '',
                'product_isbn' => '',
                'product_upc' => '',
                'product_mpn' => '',
                'product_reference' => 'demo_1',
                'product_supplier_reference' => '',
                'product_weight' => '0.000000',
                'id_tax_rules_group' => 1,
                'tax_computation_method' => 0,
                'tax_name' => 'TVA FR 20%',
                'tax_rate' => '20.000',
                'ecotax' => '0.000000',
                'ecotax_tax_rate' => '0.000',
                'discount_quantity_applied' => 0,
                'download_hash' => '',
                'download_nb' => 0,
                'download_deadline' => '0000-00-00 00:00:00',
                'total_price_tax_incl' => '47.800000',
                'total_price_tax_excl' => '39.833333',
                'unit_price_tax_incl' => '23.900000',
                'unit_price_tax_excl' => '19.916667',
                'total_shipping_price_tax_incl' => '0.000000',
                'total_shipping_price_tax_excl' => '0.000000',
                'purchase_supplier_price' => '0.000000',
                'original_product_price' => '23.900000',
                'original_wholesale_price' => '0.000000',
                'total_refunded_tax_excl' => '0.000000',
                'total_refunded_tax_incl' => '0.000000',
            ],
        ];
    }
}
```

## Utiliser les Fixtures dans vos Tests

```php
use ACSEO\PrestashopMigrationPlugin\Tests\Fixtures\PrestashopProductData;
use ACSEO\PrestashopMigrationPlugin\Tests\Fixtures\PrestashopCustomerData;

class MyIntegrationTest extends TestCase
{
    public function testImportProduct(): void
    {
        // Given - Données PrestaShop
        $prestashopData = PrestashopProductData::getSimpleProduct();

        // When - Import
        $product = $this->importer->import($prestashopData);

        // Then
        $this->assertEquals('demo_1', $product->getCode());
    }

    public function testImportMultipleEntities(): void
    {
        // Given - Plusieurs types d'entités
        $product = PrestashopProductData::getSimpleProduct();
        $customer = PrestashopCustomerData::getCustomer();
        $order = PrestashopOrderData::getOrder();

        // When & Then - Tests...
    }
}
```

## Bonnes Pratiques

### 1. Une Fixture par Entité

Créez un fichier par type d'entité:
- `PrestashopProductData.php`
- `PrestashopCustomerData.php`
- `PrestashopOrderData.php`
- `PrestashopCategoryData.php`

### 2. Méthodes Descriptives

```php
// ✅ Bon
public static function getSimpleProduct(): array
public static function getProductWithVariants(): array
public static function getDisabledProduct(): array

// ❌ Mauvais
public static function getProduct1(): array
public static function getData(): array
```

### 3. Documenter les Cas Spéciaux

```php
/**
 * Produit avec cas limite: prix négatif après réduction
 * Ce cas existe dans notre boutique et doit être géré
 */
public static function getProductWithNegativePrice(): array
{
    // ...
}
```

### 4. Versionner vos Fixtures

Si votre structure PrestaShop change:

```php
/**
 * Produit PrestaShop 1.7
 */
public static function getProductPS17(): array { /* ... */ }

/**
 * Produit PrestaShop 8.0
 */
public static function getProductPS80(): array { /* ... */ }
```

## Fixtures vs Mocks

### Fixtures (Données)
```php
$data = PrestashopProductData::getSimpleProduct();
// Retourne un array avec les données
```

### Mocks (Comportement)
```php
$repository = $this->createMock(RepositoryInterface::class);
$repository->method('findAll')->willReturn($data);
// Simule le comportement d'un repository
```

**Utilisez les deux ensemble:**
```php
// Fixtures pour les données
$prestashopData = PrestashopProductData::getSimpleProduct();

// Mocks pour le comportement
$this->repository->method('findAll')->willReturn([$prestashopData]);
```

## Maintenance

Mettez à jour vos fixtures quand:
- La structure PrestaShop change (nouvelle version)
- Vous découvrez un nouveau cas limite
- Vos tests échouent à cause de données manquantes

## Ressources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Test Data Builders Pattern](https://martinfowler.com/bliki/ObjectMother.html)
- [PrestaShop Database Schema](https://devdocs.prestashop-project.org/8/development/database/)

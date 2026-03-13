# Tests d'Intégration

Ce dossier contient des tests d'intégration qui simulent l'import complet de données PrestaShop vers Sylius.

## Différence entre tests unitaires et tests d'intégration

### Tests Unitaires (`tests/Provider/`, `tests/Validator/`, etc.)
- Testent une seule classe/méthode isolée
- Mockent toutes les dépendances
- Rapides et ciblés
- Vérifient le comportement d'un composant

### Tests d'Intégration (`tests/Integration/`)
- Testent plusieurs composants ensemble
- Simulent un workflow complet (collecte → transformation → validation → persist)
- Utilisent des données réalistes issues de PrestaShop
- Vérifient que les composants fonctionnent ensemble correctement

## Structure des Tests d'Intégration

### 1. Fixtures de Données (`tests/Fixtures/PrestashopProductData.php`)

Contient des structures de données réelles de PrestaShop:

```php
$product = PrestashopProductData::getSimpleProduct();
// Retourne un tableau avec la structure exacte d'un produit PrestaShop:
// - id_product, reference, price, active...
// - Traductions (name, description) pour chaque langue
// - Données traduites indexées par id_lang
```

**Avantages des Fixtures:**
- Données réalistes basées sur la vraie structure PrestaShop
- Réutilisables dans plusieurs tests
- Faciles à maintenir (modification centralisée)
- Documentent la structure attendue des données

### 2. Tests d'Import (`ProductImportIntegrationTest.php`)

#### Test 1: Import d'un produit simple

```php
public function testImportSimpleProductFromPrestashop()
{
    // Given - Données PrestaShop
    $prestashopProduct = PrestashopProductData::getSimpleProduct();

    // Mock des repositories PrestaShop
    $this->prestashopProductRepository
        ->method('findAll')
        ->willReturn([$prestashopProduct]);

    // When - Import
    $product = $this->importProduct($prestashopProduct);

    // Then - Vérifications
    $this->assertEquals('T-shirt imprimé colibri', $product->getName());
    $this->assertEquals('demo_1', $product->getCode());
}
```

**Ce que ce test vérifie:**
- Le produit est correctement créé
- Les traductions sont correctement importées
- Le code/référence est correct
- Le prestashop_id est bien assigné

#### Test 2: Import d'un produit avec variantes

```php
public function testImportProductWithVariantsFromPrestashop()
{
    // Given
    $prestashopProduct = PrestashopProductData::getProductWithVariants();
    $prestashopVariants = PrestashopProductData::getProductVariants();

    // When
    $product = $this->importProduct($prestashopProduct);

    // Then
    $this->assertCount(3, $product->getVariants());
    $this->assertEquals('demo_2_S_white', $firstVariant->getCode());
}
```

**Ce que ce test vérifie:**
- Les variantes sont créées
- Chaque variante a le bon code
- Le stock est correctement assigné
- Les options (taille, couleur) sont liées

#### Test 3: Test du Collector

```php
public function testCollectorReturnsCorrectProductCount()
{
    // Given
    $products = [
        PrestashopProductData::getSimpleProduct(),
        PrestashopProductData::getProductWithVariants(),
    ];

    // When
    $collector = new EntityCollector($this->prestashopProductRepository);
    $count = $collector->size();

    // Then
    $this->assertEquals(2, $count);
}
```

**Ce que ce test vérifie:**
- Le collector compte correctement les produits
- La pagination fonctionne (limit/offset)
- Les données retournées sont correctes

## Comment Créer vos Propres Tests d'Intégration

### Étape 1: Créer les Fixtures

Créez un fichier de fixtures pour votre entité (ex: `PrestashopCustomerData.php`):

```php
namespace ACSEO\PrestashopMigrationPlugin\Tests\Fixtures;

class PrestashopCustomerData
{
    public static function getCustomer(): array
    {
        return [
            'id_customer' => 1,
            'email' => 'john.doe@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'passwd' => 'hashed_password',
            'active' => 1,
            'date_add' => '2024-01-15 10:00:00',
            // ... autres champs
        ];
    }
}
```

**Astuce:** Pour obtenir la structure exacte, faites un `var_dump()` d'une vraie requête PrestaShop.

### Étape 2: Créer le Test d'Intégration

```php
namespace ACSEO\PrestashopMigrationPlugin\Tests\Integration;

use ACSEO\PrestashopMigrationPlugin\Tests\Fixtures\PrestashopCustomerData;
use PHPUnit\Framework\TestCase;

class CustomerImportIntegrationTest extends TestCase
{
    public function testImportCustomerFromPrestashop(): void
    {
        // Given - Données PrestaShop
        $prestashopCustomer = PrestashopCustomerData::getCustomer();

        // Mock repository PrestaShop
        $prestashopRepo = $this->createMock(EntityRepositoryInterface::class);
        $prestashopRepo->method('findAll')->willReturn([$prestashopCustomer]);

        // Mock repository Sylius
        $syliusRepo = $this->createMock(RepositoryInterface::class);
        $syliusRepo->method('findOneBy')->willReturn(null);

        // Mock factory
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createNew')->willReturn(new Customer());

        // When - Transformer + Provider + Validator
        $transformer = new CustomerResourceTransformer(/* ... */);
        $customer = $transformer->transform($prestashopCustomer);

        // Then - Vérifications
        $this->assertEquals('john.doe@example.com', $customer->getEmail());
        $this->assertEquals('John', $customer->getFirstName());
        $this->assertEquals(1, $customer->getPrestashopId());
    }
}
```

### Étape 3: Tester avec des Scénarios Réels

```php
public function testImportMultipleCustomersWithOrders(): void
{
    // Given - Scénario réaliste
    $customers = [
        PrestashopCustomerData::getCustomer(),
        PrestashopCustomerData::getCustomerWithOrders(),
    ];

    // When - Import batch
    $importedCount = 0;
    foreach ($customers as $customerData) {
        $customer = $this->importCustomer($customerData);
        if ($customer) $importedCount++;
    }

    // Then
    $this->assertEquals(2, $importedCount);
}
```

## Bonnes Pratiques

### 1. Données Réalistes
- Utilisez des données issues de vraies bases PrestaShop
- Incluez les cas limites (champs vides, valeurs NULL)
- Testez les données multilingues

### 2. Mock Intelligent
- Mockez uniquement ce qui est nécessaire
- Utilisez `willReturnCallback()` pour des comportements dynamiques
- Créez des helpers pour les mocks répétitifs

```php
private function mockPrestashopRepository(array $data): EntityRepositoryInterface
{
    $repo = $this->createMock(EntityRepositoryInterface::class);
    $repo->method('findAll')->willReturn($data);
    $repo->method('count')->willReturn(count($data));
    return $repo;
}
```

### 3. Tests par Scénario
- **Scénario 1:** Produit simple sans variante
- **Scénario 2:** Produit avec variantes et options
- **Scénario 3:** Produit avec images
- **Scénario 4:** Produit désactivé
- **Scénario 5:** Produit avec stock épuisé

### 4. Vérifications Complètes

```php
// Vérifie les données de base
$this->assertEquals('expected_code', $product->getCode());

// Vérifie les traductions
$this->assertEquals('French Name', $product->getTranslation('fr_FR')->getName());

// Vérifie les relations
$this->assertCount(3, $product->getVariants());
$this->assertTrue($product->hasTaxons());

// Vérifie les métadonnées
$this->assertEquals(123, $product->getPrestashopId());
$this->assertNotNull($product->getPrestashopCreatedAt());
```

## Exemples de Fixtures Réalistes

### Obtenir les Données de PrestaShop

Connectez-vous à votre base PrestaShop et exécutez:

```sql
-- Structure d'un produit
SELECT * FROM ps_product WHERE id_product = 1;
SELECT * FROM ps_product_lang WHERE id_product = 1;
SELECT * FROM ps_product_attribute WHERE id_product = 1;
SELECT * FROM ps_stock_available WHERE id_product = 1;

-- Structure d'un client
SELECT * FROM ps_customer WHERE id_customer = 1;
SELECT * FROM ps_address WHERE id_customer = 1;

-- Structure d'une catégorie
SELECT * FROM ps_category WHERE id_category = 4;
SELECT * FROM ps_category_lang WHERE id_category = 4;
```

Copiez les résultats dans vos fixtures:

```php
public static function getProductFromDatabase(): array
{
    return [
        // Collez ici les résultats de votre requête SQL
        'id_product' => 1,
        'reference' => 'ABC123',
        // ...
    ];
}
```

## Exécuter les Tests d'Intégration

```bash
# Tous les tests d'intégration
vendor/bin/phpunit tests/Integration/

# Test spécifique
vendor/bin/phpunit tests/Integration/ProductImportIntegrationTest.php

# Test avec verbose
vendor/bin/phpunit --testdox tests/Integration/
```

## Debugging

Si un test échoue:

1. **Affichez les données mockées:**
```php
dump($prestashopProduct);
dump($syliusProduct);
```

2. **Vérifiez les appels aux mocks:**
```php
$this->prestashopRepo->expects($this->once())->method('findAll');
```

3. **Activez le mode verbose:**
```bash
vendor/bin/phpunit --testdox --verbose
```

## Avantages des Tests d'Intégration

- ✅ Détectent les problèmes d'intégration entre composants
- ✅ Valident le workflow complet de migration
- ✅ Documentent comment utiliser l'API
- ✅ Permettent de tester avec des données réelles
- ✅ Augmentent la confiance avant un déploiement en production

## Ajouter des Tests pour Vos Cas d'Usage

Créez des fixtures basées sur VOS données PrestaShop pour tester des scénarios spécifiques à votre boutique:

```php
// tests/Fixtures/MyShopData.php
class MyShopData
{
    public static function getComplexProduct(): array
    {
        // Vos données réelles de production
        return [/* ... */];
    }
}
```

Cela garantit que la migration fonctionnera avec VOS données spécifiques.

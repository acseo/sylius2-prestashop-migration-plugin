<?php

declare(strict_types=1);

namespace ACSEO\PrestashopMigrationPlugin\Tests\Integration;

use ACSEO\PrestashopMigrationPlugin\DataCollector\EntityCollector;
use ACSEO\PrestashopMigrationPlugin\Repository\EntityRepositoryInterface;
use ACSEO\PrestashopMigrationPlugin\Tests\Fixtures\PrestashopProductData;
use PHPUnit\Framework\TestCase;

/**
 * Test d'intégration simplifié pour la collecte de données PrestaShop
 * Se concentre uniquement sur la partie Collection avec des données mockées réalistes
 */
class DataCollectionIntegrationTest extends TestCase
{
    public function testCollectSimpleProductFromPrestashop(): void
    {
        // Given - Données PrestaShop réelles
        $prestashopProduct = PrestashopProductData::getSimpleProduct();

        // Mock du repository PrestaShop
        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('findAll')->willReturn([$prestashopProduct]);
        $repository->method('count')->willReturn(1);

        // When - Collecte des données
        $collector = new EntityCollector($repository);
        $data = $collector->collect(10, 0);
        $count = $collector->size();

        // Then - Vérifications
        $this->assertEquals(1, $count);
        $this->assertCount(1, $data);

        // Vérifier la structure des données collectées
        $product = $data[0];
        $this->assertEquals(1, $product['id_product']);
        $this->assertEquals('demo_1', $product['reference']);
        $this->assertEquals('23.900000', $product['price']);
        $this->assertEquals(1, $product['active']);

        // Vérifier les traductions
        $this->assertArrayHasKey('name', $product);
        $this->assertIsArray($product['name']);
        $this->assertEquals('T-shirt imprimé colibri', $product['name'][1]);
        $this->assertEquals('Hummingbird printed t-shirt', $product['name'][2]);

        // Vérifier les descriptions
        $this->assertArrayHasKey('description', $product);
        $this->assertStringContainsString('classique intemporel', $product['description'][1]);
        $this->assertStringContainsString('timeless classic', $product['description'][2]);
    }

    public function testCollectProductWithVariantsFromPrestashop(): void
    {
        // Given - Produit avec variantes
        $prestashopProduct = PrestashopProductData::getProductWithVariants();
        $prestashopVariants = PrestashopProductData::getProductVariants();

        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('findAll')->willReturn([$prestashopProduct]);
        $repository->method('count')->willReturn(1);

        // When
        $collector = new EntityCollector($repository);
        $data = $collector->collect(10, 0);

        // Then
        $product = $data[0];
        $this->assertEquals(2, $product['id_product']);
        $this->assertEquals('demo_2', $product['reference']);
        $this->assertEquals('35.900000', $product['price']);

        // Vérifier les traductions
        $this->assertEquals('Pull brodé ours brun', $product['name'][1]);
        $this->assertEquals('Brown bear embroidered sweater', $product['name'][2]);
    }

    public function testCollectMultipleProductsWithPagination(): void
    {
        // Given - Plusieurs produits
        $products = [
            PrestashopProductData::getSimpleProduct(),
            PrestashopProductData::getProductWithVariants(),
        ];

        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('count')->willReturn(count($products));

        // Simuler la pagination
        $repository->method('findAll')
            ->willReturnCallback(function ($limit, $offset) use ($products) {
                return array_slice($products, $offset, $limit);
            });

        // When - Page 1 (2 produits)
        $collector = new EntityCollector($repository);
        $page1 = $collector->collect(2, 0);

        // Then
        $this->assertCount(2, $page1);
        $this->assertEquals('demo_1', $page1[0]['reference']);
        $this->assertEquals('demo_2', $page1[1]['reference']);

        // When - Page 2 (vide)
        $page2 = $collector->collect(2, 2);

        // Then
        $this->assertCount(0, $page2);
    }

    public function testCollectProductsWithRealDataStructure(): void
    {
        // Given - Données avec toute la structure PrestaShop
        $product = PrestashopProductData::getSimpleProduct();

        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('findAll')->willReturn([$product]);
        $repository->method('count')->willReturn(1);

        // When
        $collector = new EntityCollector($repository);
        $collected = $collector->collect(10, 0);

        // Then - Vérifier tous les champs importants
        $p = $collected[0];

        // Champs de base
        $this->assertArrayHasKey('id_product', $p);
        $this->assertArrayHasKey('reference', $p);
        $this->assertArrayHasKey('price', $p);
        $this->assertArrayHasKey('active', $p);

        // Dimensions
        $this->assertArrayHasKey('width', $p);
        $this->assertArrayHasKey('height', $p);
        $this->assertArrayHasKey('depth', $p);
        $this->assertArrayHasKey('weight', $p);

        // Codes-barres
        $this->assertArrayHasKey('ean13', $p);
        $this->assertArrayHasKey('isbn', $p);
        $this->assertArrayHasKey('upc', $p);

        // Stock
        $this->assertArrayHasKey('available_for_order', $p);
        $this->assertArrayHasKey('minimal_quantity', $p);

        // SEO pour chaque langue
        $this->assertArrayHasKey('meta_description', $p);
        $this->assertArrayHasKey('meta_keywords', $p);
        $this->assertArrayHasKey('link_rewrite', $p);

        // Dates
        $this->assertArrayHasKey('date_add', $p);
        $this->assertArrayHasKey('date_upd', $p);
    }

    public function testCollectCategoriesStructure(): void
    {
        // Given
        $categories = PrestashopProductData::getCategories();

        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('findAll')->willReturn($categories);
        $repository->method('count')->willReturn(count($categories));

        // When
        $collector = new EntityCollector($repository);
        $data = $collector->collect(10, 0);

        // Then
        $category = $data[0];
        $this->assertEquals(4, $category['id_category']);
        $this->assertEquals(2, $category['id_parent']);
        $this->assertEquals('Hommes', $category['name'][1]);
        $this->assertEquals('Men', $category['name'][2]);
        $this->assertEquals('hommes', $category['link_rewrite'][1]);
        $this->assertEquals('men', $category['link_rewrite'][2]);
    }

    public function testCollectStockDataStructure(): void
    {
        // Given
        $stock = PrestashopProductData::getStockAvailable();

        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('findAll')->willReturn($stock);
        $repository->method('count')->willReturn(count($stock));

        // When
        $collector = new EntityCollector($repository);
        $data = $collector->collect(10, 0);

        // Then
        $this->assertCount(3, $data);

        // Premier stock
        $stock1 = $data[0];
        $this->assertEquals(1, $stock1['id_stock_available']);
        $this->assertEquals(1, $stock1['id_product']);
        $this->assertEquals(1, $stock1['id_product_attribute']);
        $this->assertEquals(300, $stock1['quantity']);

        // Deuxième stock
        $stock2 = $data[1];
        $this->assertEquals(2, $stock2['id_product']);
        $this->assertEquals(50, $stock2['quantity']);
    }

    public function testCollectAttributeGroupsStructure(): void
    {
        // Given
        $attributeGroups = PrestashopProductData::getAttributeGroups();

        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('findAll')->willReturn($attributeGroups);

        // When
        $collector = new EntityCollector($repository);
        $data = $collector->collect(10, 0);

        // Then
        $this->assertCount(2, $data);

        // Groupe Taille
        $sizeGroup = $data[0];
        $this->assertEquals(1, $sizeGroup['id_attribute_group']);
        $this->assertEquals(0, $sizeGroup['is_color_group']);
        $this->assertEquals('select', $sizeGroup['group_type']);
        $this->assertEquals('Taille', $sizeGroup['name'][1]);
        $this->assertEquals('Size', $sizeGroup['name'][2]);

        // Groupe Couleur
        $colorGroup = $data[1];
        $this->assertEquals(2, $colorGroup['id_attribute_group']);
        $this->assertEquals(1, $colorGroup['is_color_group']);
        $this->assertEquals('color', $colorGroup['group_type']);
        $this->assertEquals('Couleur', $colorGroup['name'][1]);
        $this->assertEquals('Color', $colorGroup['name'][2]);
    }

    public function testCollectAttributesStructure(): void
    {
        // Given
        $attributes = PrestashopProductData::getAttributes();

        $repository = $this->createMock(EntityRepositoryInterface::class);
        $repository->method('findAll')->willReturn($attributes);

        // When
        $collector = new EntityCollector($repository);
        $data = $collector->collect(10, 0);

        // Then
        $this->assertCount(5, $data);

        // Taille S
        $sizeS = $data[0];
        $this->assertEquals(1, $sizeS['id_attribute']);
        $this->assertEquals(1, $sizeS['id_attribute_group']);
        $this->assertEquals('S', $sizeS['name'][1]);
        $this->assertEquals('', $sizeS['color']);

        // Couleur Blanc
        $colorWhite = $data[3];
        $this->assertEquals(4, $colorWhite['id_attribute']);
        $this->assertEquals(2, $colorWhite['id_attribute_group']);
        $this->assertEquals('Blanc', $colorWhite['name'][1]);
        $this->assertEquals('White', $colorWhite['name'][2]);
        $this->assertEquals('#FFFFFF', $colorWhite['color']);

        // Couleur Noir
        $colorBlack = $data[4];
        $this->assertEquals('#000000', $colorBlack['color']);
    }
}

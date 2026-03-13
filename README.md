# PrestaShop to Sylius Migration Plugin

This plugin provides a complete migration solution from PrestaShop to Sylius 2.x, handling all major entities including products, customers, orders, and configurations.

## Features

- Complete data migration from PrestaShop to Sylius
- Support for Sylius 2.x and Symfony 7
- Automatic duplicate detection and handling
- Dry-run mode for safe testing
- Migration status checking
- Translatable entities support
- Image migration for products
- Extensible architecture with decorators

## Installation

### 1. Add the plugin to your Sylius project

```bash
composer require acseo/sylius2-prestashop-migration-plugin
```

### 2. Enable the plugin

Add the plugin to your `config/bundles.php`:

```php
return [
    // ...
    ACSEO\PrestashopMigrationPlugin\PrestashopMigrationPlugin::class => ['all' => true],
];
```

### 3. Configure the PrestaShop database connection

Create or update `config/packages/doctrine.yaml`:

```yaml
doctrine:
    dbal:
        connections:
            default:
                # Your Sylius database configuration
                url: '%env(resolve:DATABASE_URL)%'

            prestashop:
                url: '%env(resolve:PRESTASHOP_DATABASE_URL)%'
                driver: 'pdo_mysql'
```

Add the PrestaShop database URL to your `.env` file:

```
PRESTASHOP_DATABASE_URL="mysql://user:password@localhost:3306/prestashop_db?serverVersion=8.0"
```

### 4. Configure the plugin

Create `config/packages/prestashop_migration.yaml`:

```yaml
parameters:
    prestashop.prefix: 'ps_'
    prestashop.public_directory: '%kernel.project_dir%/public/prestashop'
    prestashop.tmp_directory: '%kernel.project_dir%/var/tmp/prestashop'
```

Adjust the `prestashop.prefix` parameter according to your PrestaShop table prefix.

## Configuration

### Add PrestashopTrait to your entities

For each entity you want to migrate, add the `PrestashopTrait` to track the PrestaShop ID:

```php
<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use ACSEO\PrestashopMigrationPlugin\Entity\PrestashopTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct
{
    use PrestashopTrait;
}
```

Apply this trait to the following entities:
- `App\Entity\Product\Product`
- `App\Entity\Product\ProductVariant`
- `App\Entity\Product\ProductOption`
- `App\Entity\Product\ProductOptionValue`
- `App\Entity\Taxonomy\Taxon`
- `App\Entity\Customer\Customer`
- `App\Entity\Addressing\Address`
- `App\Entity\Addressing\Country`
- `App\Entity\Addressing\Zone`
- `App\Entity\Currency\Currency`
- `App\Entity\Locale\Locale`
- `App\Entity\Channel\Channel`
- `App\Entity\Taxation\TaxCategory`
- `App\Entity\Taxation\TaxRate`
- `App\Entity\User\AdminUser`

### Implement ProductVariantInterface (for Product Variants only)

```php
<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use ACSEO\PrestashopMigrationPlugin\Entity\PrestashopTrait;
use ACSEO\PrestashopMigrationPlugin\Entity\Product\ProductVariantInterface as PrestashopProductVariantInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements PrestashopProductVariantInterface
{
    use PrestashopTrait;

    public function hasProduct(): bool
    {
        return $this->product !== null;
    }
}
```

### Generate and run migrations

```bash
# Generate the migration for the new fields
php bin/console doctrine:migrations:diff

# Review the generated migration, then execute it
php bin/console doctrine:migrations:migrate
```

## Usage

### Check Migration Status

Before running any migration, check the current status:

```bash
php bin/console prestashop:migration:check
```

This command displays:
- Number of entities in PrestaShop
- Number of entities in Sylius
- Number of migrated entities (with prestashop_id)
- Migration status for each entity type
- Recommended commands to run

Example output:

```
PrestaShop to Sylius Migration Status Check
===========================================

+-----------------------+------------+--------------+----------+--------+-------------------------------------+
| Entity Type           | PrestaShop | Sylius Total | Migrated | Status | Command to Run                      |
+-----------------------+------------+--------------+----------+--------+-------------------------------------+
| Locales/Languages     | 2          | 8            | 0        | MISSING| php bin/console prestashop:migration:locale |
| Currencies            | 3          | 9            | 0        | MISSING| php bin/console prestashop:migration:currency |
| Countries             | 241        | 241          | 241      | OK     | -                                   |
| Products              | 150        | 106          | 0        | MISSING| php bin/console prestashop:migration:product |
+-----------------------+------------+--------------+----------+--------+-------------------------------------+
```

### Dry-Run Mode

Test any migration command without writing to the database:

```bash
php bin/console prestashop:migration:locale --dry-run
php bin/console prestashop:migration:currency --dry-run
php bin/console prestashop:migration:product --dry-run
```

Dry-run mode will:
- Execute all validation logic
- Show what would be migrated
- Display any validation errors
- Report statistics
- NOT write anything to the database

### Run Individual Migrations

Migrate entities in the recommended order:

```bash
# 1. Base configuration
php bin/console prestashop:migration:locale
php bin/console prestashop:migration:currency
php bin/console prestashop:migration:country
php bin/console prestashop:migration:zone
php bin/console prestashop:migration:channel

# 2. Taxation
php bin/console prestashop:migration:tax_category
php bin/console prestashop:migration:tax_rate

# 3. Products
php bin/console prestashop:migration:taxon
php bin/console prestashop:migration:product_option
php bin/console prestashop:migration:product_option_value
php bin/console prestashop:migration:product
php bin/console prestashop:migration:product_variant

# 4. Customers and orders
php bin/console prestashop:migration:customer
php bin/console prestashop:migration:address
php bin/console prestashop:migration:admin_user

# 5. Product images (optional, can take time)
php bin/console prestashop:migration:product:images
```

### Run Complete Migration

To migrate everything in one command:

```bash
php bin/console prestashop:migration:all
```

This command runs all migrations in the correct order.

### Force Mode - DANGER: DATA LOSS

The `prestashop:migration:all` command supports a `--force` option that completely erases and recreates your Sylius database before migration.

**WARNING: THIS WILL PERMANENTLY DELETE ALL DATA IN YOUR SYLIUS DATABASE**

```bash
# DANGER: This will erase your entire Sylius database
php bin/console prestashop:migration:all --force
```

#### What the --force option does:

1. **Drops the entire Sylius database** (`doctrine:database:drop --force`)
2. **Recreates an empty database** (`doctrine:database:create`)
3. **Recreates all database tables** (`doctrine:schema:update --force`)
4. **Runs all migrations** from PrestaShop to the fresh database

#### When to use --force:

- **Fresh installation only**: When setting up Sylius for the first time with PrestaShop data
- **Development/testing**: On development or staging environments for clean tests
- **Complete restart**: When you want to completely restart the migration process

#### When NOT to use --force:

- **Production environments**: NEVER use this on a live production site
- **Existing Sylius data**: If you have any Sylius data you want to keep
- **Partial migrations**: If you want to update or add data without losing existing content
- **Customer orders**: If you have any order history or customer data in Sylius

#### Safety measures:

The command will ask for confirmation before proceeding:

```
The database will be erase before the migration. Are you sure you want continue ? (N/y)
```

You must explicitly type "y" to proceed. The default answer is "No" to prevent accidental data loss.

#### Recommended alternative:

Instead of using `--force`, use the standard migration process which:
- Preserves existing Sylius data
- Handles duplicates intelligently
- Updates entities instead of replacing them
- Allows incremental migrations

```bash
# Recommended: Safe migration that preserves existing data
php bin/console prestashop:migration:all
```

**IMPORTANT**: Always backup your database before running any migration, especially with the `--force` option.

## Duplicate Detection

The plugin automatically handles duplicate entities to prevent unique constraint violations.

### How it works

When migrating entities like Locales, Currencies, or Countries, the plugin:

1. Checks if an entity with the same `prestashop_id` already exists
2. If not found, checks if an entity with the same unique identifier (code, ISO code, etc.) exists
3. If an existing entity is found, it reuses it instead of creating a duplicate
4. The `prestashop_id` is set on the existing entity to track the migration

### Example: Country Migration

**Scenario**: Your Sylius installation already has 241 countries, and PrestaShop also has 241 countries.

**Without duplicate detection**:
- Migration would fail with "Duplicate entry" errors
- You would need to manually delete or map entities

**With duplicate detection**:
- The plugin finds existing countries by ISO code
- Reuses existing Sylius countries
- Sets the `prestashop_id` on matched countries
- Creates only truly new countries
- Result: No duplicates, clean migration

### Supported duplicate detection

The following entities have smart duplicate detection:

- **Locale**: Matches by locale code (with automatic transformation, e.g., `fr_FR` → `fr-fr`)
- **Currency**: Matches by currency code (e.g., `EUR`, `USD`)
- **Country**: Matches by ISO country code (e.g., `FR`, `US`)

## Advanced Customization

### Creating Custom Providers

You can create custom providers to handle entity resolution:

```php
<?php

namespace App\Provider;

use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use ACSEO\PrestashopMigrationPlugin\Provider\ResourceProviderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CustomResourceProvider implements ResourceProviderInterface
{
    private ResourceProviderInterface $decorated;
    private RepositoryInterface $repository;

    public function __construct(
        ResourceProviderInterface $decorated,
        RepositoryInterface $repository
    ) {
        $this->decorated = $decorated;
        $this->repository = $repository;
    }

    public function getResource(ModelInterface $model): ResourceInterface
    {
        // Try default behavior first
        $resource = $this->decorated->getResource($model);

        // Add custom logic here
        if ($resource->getId() === null) {
            // Try to find existing entity by custom criteria
            $existing = $this->repository->findOneBy(['customField' => $model->customValue]);

            if ($existing !== null) {
                return $existing;
            }
        }

        return $resource;
    }
}
```

Register it in `config/services.yaml`:

```yaml
services:
    App\Provider\CustomResourceProvider:
        decorates: 'prestashop.provider.your_entity'
        arguments:
            - '@.inner'
            - '@sylius.repository.your_entity'
```

### Creating Custom Transformers

Transform PrestaShop data to Sylius format:

```php
<?php

namespace App\DataTransformer;

use ACSEO\PrestashopMigrationPlugin\DataTransformer\Resource\ResourceTransformerInterface;
use ACSEO\PrestashopMigrationPlugin\Model\ModelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class CustomResourceTransformer implements ResourceTransformerInterface
{
    private ResourceTransformerInterface $decorated;

    public function __construct(ResourceTransformerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function transform(ModelInterface $model, ResourceInterface $resource): void
    {
        // Call the original transformer
        $this->decorated->transform($model, $resource);

        // Add your custom transformations
        // Example: Set additional fields, modify data, etc.
    }
}
```

### Creating Custom Validators

Add custom validation logic:

```php
<?php

namespace App\Validator;

use ACSEO\PrestashopMigrationPlugin\Validator\ValidatorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class CustomValidator implements ValidatorInterface
{
    private ValidatorInterface $decorated;

    public function __construct(ValidatorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function validate(ResourceInterface $resource): bool
    {
        // Skip validation for existing entities
        if ($resource->getId() !== null) {
            return true;
        }

        // Call the original validator
        return $this->decorated->validate($resource);
    }
}
```

## Troubleshooting

### Unique Constraint Violations

If you encounter unique constraint errors:

1. Check if you have existing data in Sylius
2. The plugin should handle duplicates automatically for Locales, Currencies, and Countries
3. For other entities, you may need to create custom providers

### Memory Issues

For large datasets:

1. The migration processes data in batches (default: 100 items)
2. Increase PHP memory limit if needed: `php -d memory_limit=2G bin/console prestashop:migration:all`
3. Run migrations individually instead of using `migration:all`

### Migration Fails Midway

1. Check the error message for specific issues
2. Fix the data or code issue
3. Re-run the migration command - it will skip already migrated entities (those with `prestashop_id`)

### Images Not Migrating

1. Ensure `prestashop.public_directory` points to your PrestaShop installation
2. Check file permissions on both PrestaShop and Sylius directories
3. Verify that the PrestaShop images directory is accessible

## Testing

The plugin includes a comprehensive test suite using PHPUnit.

### Running Tests

```bash
# Install dependencies
composer install

# Run all tests
vendor/bin/phpunit

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage

# Run specific test file
vendor/bin/phpunit tests/Provider/Locale/LocaleResourceProviderTest.php
```

### Test Coverage

The test suite covers:

- Resource Providers (Locale, Currency, Country)
- Validators (Locale, Currency, Country)
- Entity Traits (PrestashopTrait)
- Data Collectors (EntityCollector)
- Persisters (TaxonPersister)
- Model Services (LocaleFetcher)

For more details, see [tests/README.md](tests/README.md).

## Requirements

- PHP 8.0 or higher
- Sylius 2.x
- Symfony 7.x
- PrestaShop 1.7+ or 8.x (as source)
- MySQL/MariaDB

## License

MIT License

## Credits

- Original package by Quentin Briais (Jgrasp)
- Maintained and updated by ACSEO for Sylius 2.x compatibility

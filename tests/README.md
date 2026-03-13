# Tests

This directory contains unit tests for the PrestaShop Migration Plugin.

## Running Tests

### Prerequisites

Install dependencies including PHPUnit:

```bash
composer install
```

### Run all tests

```bash
vendor/bin/phpunit
```

### Run tests with coverage (requires xdebug)

```bash
vendor/bin/phpunit --coverage-html coverage
```

### Run specific test file

```bash
vendor/bin/phpunit tests/Provider/Locale/LocaleResourceProviderTest.php
```

### Run specific test method

```bash
vendor/bin/phpunit --filter testReturnsExistingLocaleWhenFoundByCode
```

## Test Structure

The test structure mirrors the source code structure:

```
tests/
├── DataCollector/
│   └── EntityCollectorTest.php
├── Entity/
│   └── PrestashopTraitTest.php
├── Model/
│   └── LocaleFetcherTest.php
├── Persister/
│   └── TaxonPersisterTest.php
├── Provider/
│   ├── Country/
│   │   └── CountryResourceProviderTest.php
│   ├── Currency/
│   │   └── CurrencyResourceProviderTest.php
│   └── Locale/
│       └── LocaleResourceProviderTest.php
└── Validator/
    ├── Country/
    │   └── CountryValidatorTest.php
    ├── Currency/
    │   └── CurrencyValidatorTest.php
    └── Locale/
        └── LocaleValidatorTest.php
```

## Test Coverage

Current test coverage includes:

- **Resource Providers**: LocaleResourceProvider, CurrencyResourceProvider, CountryResourceProvider
- **Validators**: LocaleValidator, CurrencyValidator, CountryValidator
- **Entity Traits**: PrestashopTrait
- **Data Collectors**: EntityCollector
- **Persisters**: TaxonPersister
- **Model Services**: LocaleFetcher

## Writing New Tests

When adding new functionality, follow these guidelines:

1. Create tests in the same structure as the source code
2. Use descriptive test method names (e.g., `testReturnsExistingLocaleWhenFoundByCode`)
3. Follow the Given-When-Then pattern in test comments
4. Mock external dependencies
5. Test both success and failure scenarios
6. Aim for high code coverage

### Example Test

```php
public function testMethodDoesExpectedBehavior(): void
{
    // Given - Set up test data and mocks
    $input = 'test data';
    $expected = 'expected result';

    // When - Execute the method under test
    $result = $this->service->method($input);

    // Then - Assert the results
    $this->assertSame($expected, $result);
}
```

## PHPUnit Configuration

The PHPUnit configuration is defined in `phpunit.xml.dist` at the root of the project. You can create a local `phpunit.xml` file to override settings for your environment (this file is gitignored).

## Continuous Integration

Tests should pass before any pull request is merged. Ensure all tests pass locally:

```bash
vendor/bin/phpunit
```

All tests must pass with no failures or errors.

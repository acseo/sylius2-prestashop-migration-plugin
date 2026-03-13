.PHONY: help install test test-coverage test-doc clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	composer install

test: ## Run all tests
	vendor/bin/phpunit

test-coverage: ## Run tests with coverage report
	vendor/bin/phpunit --coverage-html coverage
	@echo "Coverage report generated in coverage/index.html"

test-doc: ## Run tests with documentation format
	vendor/bin/phpunit --testdox

clean: ## Clean generated files
	rm -rf vendor
	rm -rf coverage
	rm -rf .phpunit.cache
	rm -f composer.lock

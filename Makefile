.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: install
install:                                                              			## Install all dependencies for a development environment
	composer install

.PHONY: coding-standard-check
coding-standard-check:                                                          ## Check coding-standard compliance
	./vendor/bin/phpcs --basepath=. --standard=config/phpcs.xml
	composer validate
	composer normalize --dry-run

.PHONY: coding-standard-fix
coding-standard-fix:                                                            ## Apply automated coding standard fixes
	./vendor/bin/phpcbf --basepath=. --standard=config/phpcs.xml
	composer normalize

.PHONY: static-analysis
static-analysis:                                                                ## Run static analysis checks
	./vendor/bin/phpstan --memory-limit=1G --configuration=config/phpstan.neon

.PHONY: static-analysis-update
static-analysis-update:                                                         ## Update static analysis baselines
	./vendor/bin/phpstan --configuration=config/phpstan.neon --generate-baseline=config/phpstan-baseline.neon --allow-empty-baseline

sa-mago-analyze: ## Run Mago analyzer
	vendor/bin/mago --config config/mago.toml analyze --minimum-fail-level note --fail-on-out-of-sync-baseline

sa-mago-analyze-update: ## Update Mago analyzer baseline
	vendor/bin/mago --config tools/Mago/mago.toml analyze --generate-baseline --baseline tools/Mago/analyze-baseline.toml

sa-mago-lint: ## Run Mago linter
	vendor/bin/mago --config config/mago.toml lint --minimum-fail-level note --fail-on-out-of-sync-baseline

#sa-mago-lint-update: ## Update Mago linter baseline
#	vendor/bin/mago --config tools/Mago/mago.toml lint --generate-baseline --baseline tools/Mago/lint-baseline.toml

sa-mago-lint-fix: ## Apply Mago linter auto fixes
	vendor/bin/mago --config config/mago.toml lint --fix


.PHONY: security-analysis
security-analysis:                                                              ## Run static analysis security checks
	composer audit

.PHONY: unit-tests
unit-tests:                                                                     ## Run unit test suite
	./vendor/bin/phpunit -c config/phpunit.xml.dist

.PHONY: composer-validate                                                       ## Validate composer file
composer-validate:
	./vendor/bin/composer validate

.PHONY: check
check: coding-standard-check static-analysis security-analysis unit-tests       ## Run all checks for local development iterations

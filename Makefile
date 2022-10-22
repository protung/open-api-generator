help:                                                                           ## Shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:                                                              			## Install all dependencies for a development environment
	composer install

coding-standard-check:                                                          ## Check coding-standard compliance
	./vendor/bin/phpcs --basepath=. --standard=config/phpcs.xml
	composer validate
	composer normalize --dry-run

coding-standard-fix:                                                            ## Apply automated coding standard fixes
	./vendor/bin/phpcbf --basepath=. --standard=config/phpcs.xml
	composer normalize

static-analysis:                                                                ## Run static analysis checks
	./vendor/bin/phpstan --configuration=config/phpstan.neon
	./vendor/bin/psalm --config config/psalm.xml --no-cache

static-analysis-update:                                                         ## Update static analysis baselines
	./vendor/bin/phpstan --configuration=config/phpstan.neon --generate-baseline=config/phpstan-baseline.neon --allow-empty-baseline
	./vendor/bin/psalm --config config/psalm.xml --set-baseline=psalm.baseline.xml --show-info=true --no-cache

security-analysis:                                                              ## run static analysis security checks
	./vendor/bin/psalm -c config/psalm.xml --taint-analysis

unit-tests:                                                                     ## run unit test suite
	./vendor/bin/phpunit -c config/phpunit.xml.dist

composer-validate:
	./vendor/bin/composer validate

check: coding-standard-check static-analysis security-analysis unit-tests       ## run quick checks for local development iterations
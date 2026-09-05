default:
	echo "Nothing to do"

install:
	composer install

update:
	git pull
	composer update

format:
	vendor/bin/php-cs-fixer fix

lint:
	vendor/bin/php-cs-fixer check --diff
	vendor/bin/phpstan analyse --no-progress

test:
	vendor/bin/phpunit

check: lint test

.PHONY: default install update format lint test check

default:
	echo "Nothing to do"

install:
	composer install

update:
	git pull
	composer update

test:
	./vendor/bin/phpunit tests

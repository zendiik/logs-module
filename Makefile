cs:
	php vendor/bin/phpcs
	php vendor/bin/phpstan analyze

csf:
	php vendor/bin/phpcbf || true

phpstan:
	vendor/bin/phpstan analyze

install:
	composer install -o

update:
	composer update -o

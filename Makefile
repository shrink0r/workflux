ifdef PHP_PATH
	PHP=$(PHP_PATH)
else
	PHP=php
endif

help:

	@echo ""
	@echo "Available targets:"
	@echo "  codesniffer - create codesniffer report in 'build/reports' folder"
	@echo "  codesniffer-cli - run codesniffer and display report in console"
	@echo "  docs - generate API documentation into 'docs/api' folder"
	@echo "  install-composer - download and install composer to 'bin/composer.phar'"
	@echo "  install-dependencies-dev - install composer if necessary and install or update all vendor libraries (including --dev)"
	@echo "  tests - run all tests and create test coverage in 'build/reports"
	@echo ""
	@echo "Please make sure a 'php' executable is available via PATH environment variable or set a PHP_PATH variable directly with a path like '/usr/bin/php'."
	@echo ""
	@exit 0

phar: composer-dump-autoloads

	@./bin/compile

install-composer:

	@if [ ! -d ./bin ]; then mkdir bin; fi
	@if [ ! -f ./bin/composer.phar ]; then curl -sS http://getcomposer.org/installer | $(PHP) -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin/; fi

install-dependencies-dev:

	@make install-composer
	@$(PHP) -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" ./bin/composer.phar -- update

tests:

	@vendor/bin/phpunit tests/

docs:

	@if [ -d ./docs/api ]; then rm -rf ./docs/api; fi
	@$(PHP) vendor/bin/sami.php update ./bin/sami.cfg

code-sniffer:

	@if [ ! -d ./build/reports ]; then mkdir -p ./build/reports; fi
	-@$(PHP) ./vendor/bin/phpcs --extensions=php --report=checkstyle --report-file=./build/reports/checkstyle.xml --standard=psr2 ./src/

code-sniffer-cli:

	@./vendor/bin/phpcs -p --report=full --standard=psr2 ./src

composer-dump-autoloads: install-composer

	@./bin/composer.phar dumpautoload

.PHONY: tests docs help install-composer install-dependencies-dev code-sniffer code-sniffer-cli composer-dump-autoloads

# vim: ts=4:sw=4:noexpandtab:

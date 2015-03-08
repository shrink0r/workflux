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
	@echo "  composer - download and install/update composer to './composer.phar'"
	@echo "  docs - generate API documentation into 'docs/api' folder"
	@echo "  docs-markdown - generate markdown API documentation into 'docs/api/version' folder"
	@echo "  install - install all vendor libraries (including --dev)"
	@echo "  update - update all vendor libraries (including --dev)"
	@echo "  tests - run all tests and create test coverage in 'build/reports"
	@echo ""
	@echo "Please make sure a 'php' executable is available via PATH environment variable or set a PHP_PATH variable directly with a path like '/usr/bin/php'."
	@echo ""
	@exit 0

composer:

	@echo "[INFO] Installing or updating composer."
	@if [ -e composer.phar ]; then \
		$(PHP) composer.phar self-update; \
	else \
		curl -sS https://getcomposer.org/installer | $(PHP) -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin"; \
	fi

install: composer

	@echo "[INFO] Installing vendor libraries."
	@$(PHP) -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" composer.phar install --optimize-autoloader

update: composer

	@echo "[INFO] Updating vendor libraries."
	@$(PHP) -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" composer.phar update --optimize-autoloader

tests: folders

	@vendor/bin/phpunit tests/

folders:

	@mkdir -p ./docs/api
	@mkdir -p ./build/reports
	@mkdir -p ./build/logs
	@mkdir -p ./build/cache

docs: folders

	@$(PHP) vendor/bin/sami.php update ./sami.cfg.php

docs-markdown: folders

	@$(PHP) vendor/bin/sami.php update ./sami-md.cfg.php

code-sniffer: folders

	-@$(PHP) ./vendor/bin/phpcs --extensions=php --report=checkstyle --report-file=./build/reports/checkstyle.xml --standard=psr2 ./src/

code-sniffer-cli:

	@./vendor/bin/phpcs -p --report=full --standard=psr2 ./src

scrutinizer:

	@make tests
	@wget https://scrutinizer-ci.com/ocular.phar
	@$(PHP) ocular.phar code-coverage:upload --format=php-clover ./build/logs/clover.xml

dump-autoloads: composer

	@./bin/composer.phar dumpautoload

.PHONY: tests folders docs docs-markdown help install composer update code-sniffer code-sniffer-cli dump-autoloads scrutinizer

# vim: ts=4:sw=4:noexpandtab:

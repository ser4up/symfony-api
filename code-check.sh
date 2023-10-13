#!/bin/bash

echo "PHP Coding Standards Fixer"
vendor/bin/php-cs-fixer fix

echo "\n\nPHPStan"
vendor/bin/phpstan analyse src

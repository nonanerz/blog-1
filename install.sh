#!/usr/bin/env bash
clear

echo "1 - install"
echo "2 - exit"

read Keypress

case "$Keypress" in
1) echo "installing"
    composer install
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:update --force
    php bin/console doctrine:fixtures:load

;;
2) exit 0
;;
esac

exit 0
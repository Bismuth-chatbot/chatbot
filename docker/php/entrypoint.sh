#!/bin/sh
set -e


if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-suggest --no-interaction
fi

if [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

	mkdir -p var/cache var/log
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var
fi

exec /usr/bin/supervisord  -n

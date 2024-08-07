#!/bin/bash

mkdir -p /app/var/cache
mkdir -p /app/var/log

# Why:
# We create the mount points for local Flysystem adapter that is used by the OneUp\Uploader here to make sure they
# exist with the correct access permissions because somehow the filesystem adapter is creating them initially with
# the wrong permissions.
# Note: That error only happens in the following circumstances:
# 	* When we update the system to have another mount point (here 'var/data/persistent/audio_descriptions')
# 		* When a docker volume already exists
#   		* Then a directory is added on restart of the container with the incorrect permissions
#		* When a docker volume did not exist prior to the update
#			* Then a directory is added correctly on start up of the container
#   	* When we remove the mount directory while the container is running
#			* Then a directory is added correctly the next time an upload is performed
#
# The permissions will be set in the `setfacl` command below.
mkdir -p /app/var/data
mkdir -p /app/var/data/persistent/audio_descriptions/original
mkdir -p /app/var/data/persistent/subtitles/original
mkdir -p /app/var/data/persistent/videos/original
mkdir -p /app/public/data

setfacl -R -m u:www-data:rwX, u:"$(whoami)":rwX /app/var
setfacl -R -m u:www-data:rwX, u:"$(whoami)":rwX /app/public

set -ex

# install php dependencies for local development
if [ $LOCAL_DEVELOPMENT == 1 ]; then
	composer install --prefer-dist --no-progress --no-interaction
fi

echo "Waiting for db to be ready..."
until bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
	sleep 1
done

if ls -A migrations/*.php > /dev/null 2>&1; then
	bin/console doctrine:migrations:migrate --no-interaction || echo "!!!!!!!!! ERROR running Doctrine migrations. Run them after starting and fix the errors."
fi

if [ "$APP_ENV" != 'prod' ]; then
	# run migrations for test db
	APP_ENV=test bin/console doctrine:migrations:migrate --no-interaction

	# for local dev we need to start the test server instance
	ln -s -f /etc/nginx/nginx-e2e-test-server.conf /etc/nginx/conf.d/nginx-e2e-test-server.conf
fi

# install assets of bundles into public folder
php bin/console assets:install
# clear caches
php bin/console cache:clear

# chown of directories "www-data:www-data" so the app can write files to the FS
chown -R www-data:www-data /app/public/data/
chown -R www-data:www-data /app/var/

/usr/bin/supervisord

#!/bin/sh
set -e

# Wait until MongoDB is reachable
until php -r "try { new MongoDB\Driver\Manager('mongodb://mongo:27017'); echo 'ok'; } catch (Throwable \$e) { exit(1); }" >/dev/null 2>&1; do
  echo "Waiting for MongoDB at mongo:27017..."
  sleep 1
done

echo "MongoDB is up."

# Ensure .env exists
if [ ! -f /var/www/.env ]; then
  echo "Creating .env from .env.example..."
  cp /var/www/.env.example /var/www/.env
fi

# Generate app key if missing
if ! grep -q "^APP_KEY=base64" /var/www/.env; then
  echo "Generating application key..."
  php /var/www/artisan key:generate --force
fi

# Generate JWT secret if missing
if ! grep -q "^JWT_SECRET=" /var/www/.env; then
  echo "Generating JWT secret..."
  php /var/www/artisan jwt:secret --force || true
fi

# Run migrations
echo "Running migrations..."
php /var/www/artisan migrate --force

# Seed database
echo "Seeding database..."
php /var/www/artisan db:seed --force

# Execute the CMD passed to the container
exec "$@"

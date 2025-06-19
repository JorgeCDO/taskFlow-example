#!/bin/sh

DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}

echo "Esperando a MySQL en $DB_HOST:$DB_PORT..."

until nc -z -v -w30 $DB_HOST $DB_PORT
do
  echo "Esperando a que MySQL arranque en $DB_HOST:$DB_PORT..."
  sleep 10
done

echo "✅ MySQL está listo. Continuando..."

if [ ! -f .env ]; then
    cp .env.example .env
fi

chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

composer install
npm install

php artisan key:generate
php artisan migrate

php artisan serve --host=0.0.0.0 --port=8000

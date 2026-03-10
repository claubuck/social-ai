#!/bin/sh
set -e

# Generar APP_KEY si no existe (primera ejecución)
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "base64:" ]; then
  php artisan key:generate --force
fi

# Esperar a que Postgres esté listo (opcional, el healthcheck de compose ya lo hace)
if [ -n "${DB_HOST}" ]; then
  until php -r "
    try {
      new PDO(
        'pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE}',
        '${DB_USERNAME}',
        '${DB_PASSWORD}'
      );
      exit(0);
    } catch (Exception \$e) {
      exit(1);
    }
  " 2>/dev/null; do
    echo "Esperando PostgreSQL..."
    sleep 2
  done
fi

# Migraciones si se indica
if [ "${RUN_MIGRATE}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

exec "$@"

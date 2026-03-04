#!/usr/bin/env sh
set -e

cd /var/www

echo "[entrypoint] booting..."

# 1) .env
if [ ! -f .env ]; then
  echo "[entrypoint] .env not found -> copying from .env.example"
  cp .env.example .env
fi

# 2) vendor (composer install)
if [ ! -f vendor/autoload.php ]; then
  echo "[entrypoint] vendor missing -> composer install (this may take a while on first run)"
  composer install --no-interaction --prefer-dist
fi

# 3) APP_KEY
if ! grep -q '^APP_KEY=base64:' .env; then
  echo "[entrypoint] APP_KEY missing -> generating"
  php artisan key:generate
fi

# 4) ОПОВЕЩЕНИЕ: можно мигрировать
# (любой пользователь увидит это через `docker compose logs -f app`)
echo "[entrypoint] READY: You can now run migrations:"
echo "[entrypoint] READY: docker compose exec app php artisan migrate:fresh --seed"

# (опционально) маркер-файл для дополнительной проверки/ожидания
touch /var/www/.ready-to-migrate

# 5) стартуем приложение
echo "[entrypoint] starting laravel dev server on 0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
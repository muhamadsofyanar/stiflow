#!/usr/bin/env sh
set -eu

for extension in pdo_mysql mbstring openssl bcmath pcntl; do
    php -m | grep -qi "^${extension}$" || {
        echo "PHP extension tidak tersedia: ${extension}" >&2
        exit 1
    }
done

test -f public/build/manifest.json || {
    echo "Vite manifest tidak ditemukan." >&2
    exit 1
}

test -w storage || {
    echo "Direktori storage tidak writable." >&2
    exit 1
}

APP_KEY="${APP_KEY:-base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=}" \
DB_CONNECTION=sqlite \
DB_DATABASE=:memory: \
php artisan about --only=environment >/dev/null

echo "STIFLow runtime smoke test: OK"

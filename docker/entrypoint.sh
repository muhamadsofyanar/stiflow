#!/usr/bin/env sh
set -eu

require_runtime_config() {
    if [ -z "${APP_KEY:-}" ]; then
        echo "APP_KEY wajib diisi untuk proses produksi." >&2
        exit 1
    fi

    mkdir -p \
        storage/app/private storage/app/public \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
        storage/logs bootstrap/cache

    php artisan config:cache
    php artisan view:cache
}

case "${1:-}" in
    web)
        require_runtime_config
        exec /usr/bin/supervisord -c /etc/supervisord.conf
        ;;
    worker)
        require_runtime_config
        exec php artisan queue:work database --tries=1 --timeout=90 --max-time=3600
        ;;
    scheduler)
        require_runtime_config
        exec php artisan schedule:work
        ;;
    *)
        exec "$@"
        ;;
esac

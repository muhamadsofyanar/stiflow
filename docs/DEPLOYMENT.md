# Deployment Voucher MVP

## Prasyarat

- Docker Engine dan Compose v2 atau Coolify.
- DNS HTTPS menuju service `app` port 8080.
- MySQL 8, satu database khusus untuk satu cabang.
- Kredensial API STIFIN untuk cabang tersebut.

## Environment wajib

Salin `.env.example` menjadi `.env`. Isi minimal `APP_KEY`, `APP_URL`, `BRANCH_CODE`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `STIFIN_API_BASE_URL`, `STIFIN_AUTH_HEADER`, `STIFIN_AUTH_VALUE`, dan `STIFIN_USER_ID`. Buat key dengan `php artisan key:generate --show`; jangan memakai key dari cabang lain.

Gunakan `DB_CONNECTION=mysql`, `QUEUE_CONNECTION=database`, `APP_ENV=production`, dan `APP_DEBUG=false`. Fase Voucher MVP hanya mengaktifkan transfer manual. Jangan isi atau mengaktifkan gateway simulasi.

## Deploy pertama

```bash
docker compose -f compose.production.yaml build
docker compose -f compose.production.yaml up -d db
docker compose -f compose.production.yaml run --rm app php artisan migrate --force
docker compose -f compose.production.yaml up -d app worker scheduler
curl --fail https://DOMAIN-CABANG/up
docker compose -f compose.production.yaml exec app php artisan migrate:status
```

Masukkan `branch_settings`, produk voucher, rekening transfer, admin pertama, dan kredensial STIFIN melalui prosedur onboarding cabang. Worker memakai `--tries=1`; hasil POST yang tidak pasti harus masuk rekonsiliasi dan tidak boleh diulang otomatis.

## Update aman

1. Buat backup database dan storage sesuai `BACKUP-RESTORE.md`.
2. Build image bertag versi baru, misalnya `stiflow:v0.2.0`.
3. Jalankan migration satu kali: `docker compose -f compose.production.yaml run --rm app php artisan migrate --force`.
4. Ubah `STIFLOW_IMAGE`, lalu jalankan `docker compose -f compose.production.yaml up -d app worker scheduler`.
5. Periksa `/up`, `migrate:status`, log worker, dan satu transaksi uji non-produksi.

## Rollback

Rollback aplikasi berarti mengembalikan tag image sebelumnya. Jangan menjalankan `migrate:rollback` pada produksi kecuali migration versi tersebut telah dinyatakan reversibel dan backup baru sudah diverifikasi. Jika schema/data berubah tidak kompatibel, pulihkan database dan storage ke instance baru sesuai dokumen restore.

## Coolify

Pilih Docker Compose, gunakan `compose.production.yaml`, pasang persistent volume database dan `/var/www/html/storage`, arahkan domain hanya ke service `app:8080`, dan jangan mengekspos service `db`, `worker`, atau `scheduler`.

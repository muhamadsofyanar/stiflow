# Update STIFLow Voucher MVP di Coolify

## 1. Unggah source ke GitHub

Ekstrak ZIP terlebih dahulu. Unggah isi foldernya ke root repository GitHub, bukan file ZIP-nya. Pertahankan struktur folder seperti `app/`, `config/`, `database/`, `docker/`, `resources/`, dan `tests/`.

Jangan unggah file `.env`, folder `vendor`, folder `node_modules`, cache, atau log.

## 2. Deploy ulang dari Coolify

Pastikan Build Pack memakai Docker Compose dan file compose adalah:

```text
compose.production.yaml
```

Klik **Redeploy** lalu tunggu service `db` dan `app` berstatus healthy.

## 3. Siapkan data inti

Jalankan perintah berikut lewat SSH VPS setelah deployment selesai:

```bash
APP_CONTAINER=$(docker ps --filter "name=app-8tqx7fkrwohnuflbmnihkrxe" --format '{{.ID}}' | head -n1)

docker exec -it "$APP_CONTAINER" php artisan migrate --force
docker exec -it "$APP_CONTAINER" php artisan db:seed --class=ProductionBootstrapSeeder --force
docker exec -it "$APP_CONTAINER" php artisan optimize:clear
docker exec -it "$APP_CONTAINER" php artisan config:cache
docker exec -it "$APP_CONTAINER" php artisan view:cache
```

Seeder tersebut aman dijalankan ulang. Ia membuat data awal cabang, produk voucher, konfigurasi voucher, dan katalog permission secara idempoten; akun pengguna tidak dibuat atau dihapus.

## 4. Periksa hasil

Buka halaman berikut:

- `/up`
- `/admin`
- `/admin/products`
- `/admin/promotors`
- `/admin/orders`
- `/admin/reconciliation`
- `/admin/settings/branch`
- `/admin/courses`
- `/admin/products-catalog`
- `/admin/stifin-results`
- `/admin/campaigns`
- `/admin/templates`
- `/admin/pipelines`
- `/admin/integrations`
- `/admin/points-ledger`
- `/admin/staff/permissions`
- `/admin/audit`

Modul admin lanjutan diaktifkan dengan `STIFLOW_ADMIN_EXTENDED_MODULES=true`. Menu promotor/member yang masih eksperimental tetap disembunyikan dengan `STIFLOW_PROTOTYPE_MODULES=false`.

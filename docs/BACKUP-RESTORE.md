# Backup dan Restore

## Backup

Buat direktori bertanggal pada host yang terenkripsi. Hentikan approval pembayaran selama snapshot agar database dan file konsisten.

```bash
docker compose -f compose.production.yaml exec -T db sh -c 'exec mysqldump --single-transaction --routines --triggers -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > stiflow.sql
docker run --rm -v stiflow_stiflow_storage:/data:ro -v "$PWD":/backup alpine tar -C /data -czf /backup/stiflow-storage.tar.gz .
sha256sum stiflow.sql stiflow-storage.tar.gz > SHA256SUMS
sha256sum -c SHA256SUMS
```

Simpan salinan di lokasi lain. Backup yang belum pernah direstore bukan bukti pemulihan.

## Restore ke instance baru

Jangan menguji restore di database produksi.

```bash
sha256sum -c SHA256SUMS
docker compose -f compose.production.yaml up -d db
docker compose -f compose.production.yaml exec -T db sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < stiflow.sql
docker run --rm -v stiflow_stiflow_storage:/data -v "$PWD":/backup alpine sh -c 'cd /data && tar -xzf /backup/stiflow-storage.tar.gz'
docker compose -f compose.production.yaml up -d app worker scheduler
```

## Verifikasi hasil

- `/up` mengembalikan HTTP 200.
- `php artisan migrate:status` tidak menunjukkan migration tertunda.
- Login admin dan promotor bekerja.
- Pilih sampel order selesai, bukti pembayaran, fulfillment, STIFIN operation, dan audit log; pastikan relasinya utuh.
- Pilih file bukti pembayaran privat dan pastikan hanya pemilik/administrator berwenang yang dapat membacanya.
- Catat waktu restore, versi image, checksum, operator, serta hasil sampel pada checklist rilis.

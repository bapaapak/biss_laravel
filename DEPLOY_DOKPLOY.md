# Deploy ke VPS pakai Dokploy

Panduan ini memakai mode **Dockerfile** di Dokploy (paling simpel untuk repository ini).

## 1) Prasyarat VPS

- Dokploy sudah terpasang dan domain sudah diarahkan ke VPS.
- MySQL sudah tersedia (di VPS yang sama atau server terpisah).
- Repository ini sudah bisa diakses Dokploy (GitHub token/connection sudah di-set).

## 2) Buat Project dan Application di Dokploy

- Buat Project baru di Dokploy.
- Tambahkan Application baru dari GitHub repo ini.
- Pilih deployment type: **Dockerfile**.
- Branch: `main`.
- Dockerfile path: `Dockerfile`.
- Internal Port: `80`.

## 3) Set Environment Variables (wajib)

Isi env di panel Dokploy minimal seperti ini:

- `APP_NAME=BISS System`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://biss.tubagus.biz.id`
- `APP_KEY=base64:...` (disarankan isi tetap, jangan kosong)
- `LOG_CHANNEL=stack`
- `LOG_LEVEL=error`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1` atau host MySQL yang dipakai di VPS
- `DB_PORT=3306`
- `DB_DATABASE=db_biss`
- `DB_USERNAME=db_biss_user`
- `DB_PASSWORD=<password-db-yang-kuat>`
- `CACHE_DRIVER=file`
- `SESSION_DRIVER=file`
- `SESSION_LIFETIME=120`
- `QUEUE_CONNECTION=sync`
- `MAIL_MAILER=smtp`
- `MAIL_HOST=<smtp-host-anda>`
- `MAIL_PORT=465`
- `MAIL_USERNAME=<smtp-user-anda>`
- `MAIL_PASSWORD=<smtp-password-anda>`
- `MAIL_ENCRYPTION=ssl`
- `MAIL_FROM_ADDRESS=<email-pengirim-anda>`
- `MAIL_FROM_NAME=BISS System`
- `RUN_MIGRATIONS=true`
- `AUTO_IMPORT_SQL_DUMP=true` (hanya untuk deploy awal ketika database masih kosong)
- `SQL_DUMP_FILE=ssotoght_db_biss (1) (2).sql` (opsional, untuk memilih dump secara eksplisit)

Catatan:
- Container akan otomatis menjalankan `php artisan migrate` saat startup jika `RUN_MIGRATIONS=true`.
- Untuk deploy rolling yang lebih aman di jam sibuk, bisa set `RUN_MIGRATIONS=false`, lalu migrate manual via terminal Dokploy.
- Jika `AUTO_IMPORT_SQL_DUMP=true`, container akan mencoba import file `ssotoght_db_biss (1).sql` saat startup ketika tabel `projects` masih kosong.
- Jika `SQL_DUMP_FILE` diisi, startup akan memakai file itu. Jika tidak diisi, startup akan memprioritaskan `ssotoght_db_biss (1) (2).sql`, lalu fallback ke `ssotoght_db_biss (1).sql`.
- Setelah data sudah terisi, ubah `AUTO_IMPORT_SQL_DUMP=false` agar tidak mencoba import ulang di startup berikutnya.

## 4) Domain dan SSL

- Tambahkan domain aplikasi di Dokploy.
- Aktifkan SSL/Let's Encrypt dari panel Dokploy.

## 5) Deploy pertama

- Klik Deploy.
- Pastikan healthcheck hijau.
- Cek endpoint utama aplikasi.

## 6) Verifikasi pasca deploy

Jalankan perintah ini dari terminal container bila perlu:

- `php artisan about`
- `php artisan migrate:status`
- `php artisan optimize`

## 7) Troubleshooting cepat

- Jika muncul error `APP_KEY`:
  - isi `APP_KEY` tetap di env Dokploy (recommended), atau
  - jalankan `php artisan key:generate --show` lalu simpan hasilnya ke env.
- Jika gagal koneksi database:
  - cek `DB_HOST`, `DB_PORT`, firewall VPS, dan hak akses user MySQL.
- Jika upload file bermasalah:
  - pastikan `storage` dan `bootstrap/cache` writable (startup script sudah mengatur permission).

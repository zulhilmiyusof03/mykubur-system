# Deploy MyKubur Laravel ke Railway

Project ini guna PHP 8.4 di production kerana dependencies dalam `composer.lock` memerlukan PHP 8.4.

## 1. Push project ke GitHub

Commit fail deployment ini, kemudian push repo ke GitHub.

## 2. Buat project Railway

1. Buka Railway.
2. New Project.
3. Deploy from GitHub repo.
4. Pilih repo `mykubur-laravel`.
5. Tambah service database MySQL atau PostgreSQL.

## 3. Set environment variables

Di service Laravel, tambah variables ini:

```env
APP_NAME=MyKubur
APP_ENV=production
APP_KEY=base64:ISI_APP_KEY_DI_SINI
APP_DEBUG=false
APP_URL=https://domain-railway-anda

LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

VITE_APP_NAME="${APP_NAME}"
```

Untuk PostgreSQL, tukar `DB_CONNECTION=pgsql` dan gunakan variables PostgreSQL Railway.

## 4. Generate APP_KEY

Jalankan command ini di komputer sendiri:

```bash
php artisan key:generate --show
```

Salin output `base64:...` ke `APP_KEY` di Railway.

## 5. Deploy

Railway akan build Docker image, run migration, dan start Laravel secara automatik. Lepas deploy berjaya, buka domain Railway dan set `APP_URL` kepada domain itu.

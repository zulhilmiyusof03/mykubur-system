# MyKubur Laravel

Projek Laravel untuk Sistem MyKubur Perkuburan Islam Kampung Rantau Panjang, Mukim Kapar, Selangor.

## Jalankan di localhost

```powershell
cd c:\Flutter_Project\mykubur-laravel
php artisan serve
```

Kemudian buka:

```text
http://127.0.0.1:8000
```

## Lokasi fail sistem

- Route utama: `routes/web.php`
- Blade view: `resources/views/mykubur.blade.php`
- CSS: `public/assets/css/styles.css`
- JavaScript: `public/assets/js/app.js`
- Database MySQL: `mykubur_laravel`
- Jadual utama: `grave_records`, `grave_waris`

## Setup database MySQL

Pastikan MySQL XAMPP/Laragon hidup, kemudian jalankan:

```powershell
mysql -u root -e "CREATE DATABASE IF NOT EXISTS mykubur_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force
php artisan db:seed --force
```

## Akaun demo

- Admin: `admin@mykubur.com` / `admin123`
- Waris: `waris@mykubur.com` / `waris123`

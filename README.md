# StockPoint Jabar

StockPoint Jabar adalah aplikasi web manajemen persediaan multi-gudang untuk PT Suplai Nusantara Regional. Sistem ini dibangun menggunakan Laravel, MySQL, dan arsitektur RESTful dengan kontrol akses berbasis peran (RBAC) yang ketat.

## Fitur Utama

- **Manajemen Multi-Gudang:** Mendukung pencatatan persediaan untuk Gudang Pusat (Bandung), Gudang Transaksi (Bekasi), dan Gudang Transit (Bogor).
- **Role-Based Access Control (RBAC):**
  - **Admin Gudang:** Akses penuh semua fitur.
  - **Kepala Gudang:** Konfigurasi gudang mandiri & ROP.
  - **Staf Gudang:** Pencatatan dan mutasi transaksi harian gudang lokal.
  - **Manajer Operasional:** Akses *read-only* & ekspor laporan lintas gudang.
- **Manajemen ROP Dinamis:** Pemantauan otomatis level stok berdasarkan Average Daily Usage (ADU), Lead Time, dan Safety Stock (AMAN, MENIPIS, KRITIS).
- **Audit Terpusat:** Integritas data terjamin di mana transaksi bersifat mutlak/immutable. Semua koreksi harus melalui "Stock Adjustment" dan tercatat permanen di *audit trail*.
- **Notifikasi Pintar:** Hanya dikirim saat status barang berpindah ke tingkat KRITIS.

## Tech Stack
- Backend: PHP 8.2+, Laravel 11.x
- Frontend: HTML, CSS, JavaScript, Bootstrap
- Database: MySQL 8.x
- Server: Nginx/Apache (Rekomendasi server *production*)

---

## Prasyarat (Requirements)

Sebelum melakukan instalasi, pastikan sistem Anda memiliki lingkungan berikut:
- **PHP** >= 8.2 (ekstensi bcmath, ctype, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml)
- **Composer** >= 2.x
- **MySQL** >= 8.x (atau MariaDB setara)
- **Node.js & NPM** (opsional untuk *build assets* frontend)

---

## Panduan Instalasi (Setup)

1. **Kloning Repositori & Masuk ke Direktori Proyek**
   ```bash
   git clone <url-repo> stockpoint-jabar
   cd stockpoint-jabar
   ```

2. **Instalasi Dependensi PHP (Composer)**
   ```bash
   composer install
   ```

3. **Pengaturan Konfigurasi *Environment***
   Salin berkas konfigurasi sampel untuk menjadi konfigurasi lokal:
   ```bash
   cp .env.example .env
   ```
   *Buka berkas `.env` dan sesuaikan kredensial koneksi database MySQL:*
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=stockpoint_jabar
   DB_USERNAME=root
   DB_PASSWORD=password_db_anda
   ```

4. **Pembuatan Database**
   Buat database baru di server MySQL Anda dengan nama `stockpoint_jabar` (atau sesuai konfigurasi `DB_DATABASE`).
   ```sql
   CREATE DATABASE stockpoint_jabar;
   ```

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Migrasi dan Seed Database**
   Jalankan seluruh struktur migrasi awal dan sisipkan (seed) data default (termasuk lokasi 3 Gudang, dan 1 Akun Admin):
   ```bash
   php artisan migrate --seed
   ```
   > Setelah proses ini selesai, Anda dapat login menggunakan akun berikut:
   > - **Email:** `admin@stockpoint.id`
   > - **Password:** `Admin123!`

7. **Menjalankan Server Lokal**
   Gunakan Artisan untuk menyajikan aplikasi pada mode pengembangan:
   ```bash
   php artisan serve
   ```
   Akses aplikasi web di browser: `http://localhost:8000`

## Pedoman Keamanan Khusus Pengembang
- **Password:** Selalu di-*hash* menggunakan bcrypt, dilarang menyimpan di *plaintext*.
- **Middleware RBAC:** Otorisasi diverifikasi secara wajib di sisi server/API, bukan hanya penyembunyian antarmuka UI. Upaya bypass akan mengembalikan kode **HTTP 403 Forbidden**.
- **Transaksi Log:** Tidak diizinkan membuat migrasi yang menghapus entitas log `stock`.

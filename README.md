# Sistem Informasi Disporabudpar Kabupaten Jember

Website profil dan informasi **Dinas Kepemudaan, Olahraga, Kebudayaan, dan Pariwisata (Disporabudpar) Kabupaten Jember**, lengkap dengan panel admin untuk mengelola wisata dan berita. Dibangun dengan **Laravel 13** dan **Livewire 4**.

## Fitur

**Situs publik**

- Beranda dengan slider, wisata unggulan, dan berita terbaru
- Peta wisata interaktif (**Leaflet + OpenStreetMap**) dengan penanda per tempat wisata dan filter kategori
- Daftar berita dengan filter kategori dan paginasi, serta halaman detail berita
- Mode terang/gelap

**Panel admin** (`/admin`)

- Login dengan peran `admin` dan `editor`, serta status akun aktif/nonaktif
- Dashboard: jumlah wisata, hotel, berita, kesenian, dan ekraf, serta grafik garis kunjungan hotel per bulan (domestik dan mancanegara, angka di tiap titik) dengan pilihan tahun
- Kelola wisata (form dan detail dalam modal): tambah, ubah, detail, hapus (soft delete), tandai unggulan, deskripsi singkat, link Google Maps, gambar utama, dan galeri foto (banyak foto sekaligus, dengan keterangan per foto)
- Kelola hotel (form dan detail dalam modal): tambah, ubah, detail, hapus permanen beserta semua file gambarnya, gambar utama wajib, galeri foto, link Google Maps, website, dan telepon
- Kelola berita (form dan detail dalam modal): tulis, ubah, detail, hapus (soft delete), status draf/terbit, unggah gambar sampul
- Kelola kesenian (form dan detail dalam modal, tanpa gambar): tambah, ubah, detail, hapus, pencarian, dan filter kategori
- Kelola ekonomi kreatif (form dan detail dalam modal, tanpa gambar): tambah, ubah, detail, hapus, pencarian, dan filter subsektor
- Data kunjungan wisata per bulan (domestik dan mancanegara): tambah, ubah, detail, hapus, filter tahun/bulan/wisata, ringkasan total sesuai filter, dan tolak data ganda untuk wisata + bulan + tahun yang sama
- Data kunjungan hotel per bulan, dengan fitur yang sama seperti data kunjungan wisata

**Dalam pengembangan:** halaman profil admin (ganti password). Isi halaman Profil publik masih berupa teks penanda `[ISI ... DI SINI]` di `resources/views/livewire/publik/profil/`.

## Kebutuhan sistem

| Kebutuhan | Versi | Keterangan |
|---|---|---|
| **PHP** | **8.3 atau lebih baru** | Dikembangkan dengan PHP 8.5 |
| **Laravel** | **^13.17** | Terpasang otomatis lewat Composer (dikembangkan dengan 13.38) |
| **Livewire** | **^4.4** | Terpasang otomatis lewat Composer |
| **MySQL** | **8.0 atau lebih baru** | Migrasi memakai indeks FULLTEXT dan constraint CHECK. MariaDB belum diuji |
| **Composer** | 2.x | |
| **Git** | | |
| Node.js + npm | 20.19+ atau 22.12+ | **Opsional.** Halaman memakai CSS statis di `public/css`, jadi aplikasi jalan tanpa `npm` |

Ekstensi PHP yang harus aktif: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`.

Peta, grafik, dan font dimuat dari CDN (Leaflet dan Chart.js dari cdnjs, plugin label grafik dari jsDelivr, tile dari OpenStreetMap, font dari Google Fonts), jadi **perlu koneksi internet** saat membuka situs.

Di Windows, paling mudah memakai [Laragon](https://laragon.org/) atau XAMPP, yang sudah menyertakan PHP, MySQL, dan Composer.

## Instalasi

### 1. Ambil kode

```bash
git clone https://github.com/USERNAME/NAMA-REPO.git
cd NAMA-REPO
```

### 2. Pasang dependensi PHP

```bash
composer install
```

### 3. Siapkan file `.env`

```bash
# Linux / macOS
cp .env.example .env

# Windows (CMD)
copy .env.example .env
```

Lalu buat application key:

```bash
php artisan key:generate
```

### 4. Buat database

Buat database kosong bernama `disporabudpar` (lewat phpMyAdmin, HeidiSQL, atau terminal):

```sql
CREATE DATABASE disporabudpar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Atur `.env`

Buka `.env` dan sesuaikan bagian ini:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=disporabudpar
DB_USERNAME=root
DB_PASSWORD=

# Akun admin pertama (WAJIB diisi sebelum langkah 6)
ADMIN_NAME="Administrator"
ADMIN_EMAIL=admin@disporabudpar.test
ADMIN_PASSWORD=isi_password_yang_kuat
```

> Kalau `ADMIN_PASSWORD` kosong, akun admin **tidak** dibuat.

### 6. Migrasi dan isi data awal

```bash
php artisan migrate --seed
```

Perintah ini membuat semua tabel, lalu mengisi data awal: akun admin, daftar kecamatan, kategori, bidang, halaman statis, dan kontak instansi.

### 7. Tautkan folder penyimpanan gambar

```bash
php artisan storage:link
```

Langkah ini **wajib**, kalau tidak gambar wisata, galeri wisata, dan berita yang diunggah lewat admin tidak akan tampil.

### 8. Jalankan

```bash
php artisan serve
```

Buka <http://127.0.0.1:8000>. Alamat ini otomatis diarahkan ke `/publik/beranda`.

## Data contoh (opsional)

Supaya beranda dan peta tidak kosong saat mencoba:

```bash
php artisan db:seed --class=WisataSeeder
php artisan db:seed --class=BeritaSeeder
```

Jalankan **setelah** langkah 6, dan cukup sekali. Semua data contoh bisa diubah atau dihapus lewat panel admin.

## Alamat penting

| Alamat | Keterangan |
|---|---|
| `/publik/beranda` | Beranda |
| `/publik/peta-wisata` | Peta wisata dan hotel (Leaflet) |
| `/publik/wisata/{slug}` | Detail wisata |
| `/publik/hotel/{id}` | Detail hotel |
| `/publik/kunjungan-wisata` | Data kunjungan wisata (grafik dan rekap) |
| `/publik/kunjungan-hotel` | Data kunjungan hotel (grafik dan rekap) |
| `/publik/kebudayaan` | Data kesenian (Kebudayaan) |
| `/publik/kebudayaan/{id}` | Detail kesenian |
| `/publik/ekonomi-kreatif` | Data ekonomi kreatif |
| `/publik/ekonomi-kreatif/{id}` | Detail usaha ekonomi kreatif |
| `/publik/profil/tentang-kami` | Profil: Tentang Kami (statis) |
| `/publik/profil/visi-misi` | Profil: Visi Misi (statis) |
| `/publik/profil/struktur-organisasi` | Profil: Struktur Organisasi (gambar `public/images/struktur-organisasi.png`) |
| `/publik/profil/tugas-dan-fungsi` | Profil: Tugas dan Fungsi (statis) |
| `/publik/berita` | Daftar berita |
| `/publik/berita/{slug}` | Detail berita |
| `/admin/login` | Login admin |
| `/admin/dashboard` | Dashboard admin |
| `/admin/wisata` | Kelola wisata dan galerinya |
| `/admin/hotel` | Kelola hotel dan galerinya |
| `/admin/kesenian` | Kelola data kesenian |
| `/admin/ekonomi-kreatif` | Kelola data ekonomi kreatif |
| `/admin/kunjungan-wisata` | Kelola data kunjungan wisata |
| `/admin/kunjungan-hotel` | Kelola data kunjungan hotel |
| `/admin/berita` | Kelola berita |

Masuk ke `/admin/login` dengan `ADMIN_EMAIL` dan `ADMIN_PASSWORD` yang Anda isi di `.env`.

## Pengaturan footer situs publik

Isi footer (deskripsi dinas, link media sosial, Tautan Cepat, alamat/telepon/email, dan peta kantor) ada di `config/site.php`. Nilainya bisa diubah langsung di file itu atau lewat `.env`, misalnya:

```env
SITE_INSTAGRAM=https://instagram.com/akun_dinas
SITE_EMAIL=alamat@email-dinas
SITE_PETA_EMBED="https://www.google.com/maps/embed?pb=..."
```

Media sosial yang URL-nya kosong tidak ditampilkan. Setelah mengubah `.env` di server produksi, jalankan `php artisan config:clear` (atau `php artisan optimize` lagi).

Penghitung pengunjung di footer mencatat setiap sesi browser sekali per hari (tabel `kunjungan_situs`), tanpa menghitung bot dan admin yang sedang login. Angkanya di-cache 5 menit.

## Struktur basis data

22 tabel domain dan 17 relasi. Diagram lengkap (dengan semua kolom) ada di [`docs/ERD-Disporabudpar.png`](docs/ERD-Disporabudpar.png), dan versi vektornya di [`docs/ERD-Disporabudpar.svg`](docs/ERD-Disporabudpar.svg).

![ERD](docs/ERD-Disporabudpar.png)

## Struktur folder penting

```
app/
  Livewire/Admin/        Halaman panel admin (Dashboard, Wisata, Berita, ...)
  Livewire/Publik/       Halaman situs publik (Beranda, PetaWisata, Berita, ...)
  Models/                Model Eloquent
  Http/Middleware/       PastikanAdminAktif (hanya akun aktif yang boleh masuk admin)
database/
  migrations/            Skema basis data
  seeders/               Data awal dan data contoh
public/css/              publik.css dan admin.css
resources/views/
  layouts/               publik.blade.php, admin.blade.php, admin-login.blade.php
  livewire/              Tampilan tiap halaman
routes/web.php           Semua route
```

## Pengembangan

```bash
php artisan test                # jalankan test
./vendor/bin/pint               # rapikan gaya kode
php artisan optimize:clear      # bersihkan cache (config, route, view)
```

## Deploy ke server (produksi)

Di `.env` server:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.go.id
```

Lalu:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Arahkan *document root* web server ke folder `public/`. Jangan pernah mengunggah file `.env` ke GitHub, dan **ganti password admin** setelah login pertama.

## Masalah umum

| Pesan error | Penyebab dan solusi |
|---|---|
| `No application encryption key has been specified` | Jalankan `php artisan key:generate` |
| `SQLSTATE[HY000] [1049] Unknown database` | Database belum dibuat atau nama di `DB_DATABASE` salah (langkah 4 dan 5) |
| `SQLSTATE[HY000] [1045] Access denied` | `DB_USERNAME` atau `DB_PASSWORD` salah |
| `Table 'sessions' doesn't exist` | Migrasi belum dijalankan: `php artisan migrate` |
| Tidak bisa login admin | `ADMIN_PASSWORD` kosong saat seeding. Isi di `.env`, lalu `php artisan db:seed --class=AdminSeeder` |
| Gambar yang diunggah tidak tampil | Jalankan `php artisan storage:link` |
| `Route [...] not defined` | Halaman tersebut belum dibuat. Lihat bagian "Dalam pengembangan" |
| Perubahan tampilan tidak muncul | `php artisan optimize:clear` |

## Lisensi

Dibangun di atas [Laravel](https://laravel.com), yang berlisensi [MIT](https://opensource.org/licenses/MIT).

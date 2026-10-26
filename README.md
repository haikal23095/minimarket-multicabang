# Minimarket Multi-Cabang

Aplikasi web POS minimarket berbasis Laravel 13 dan Microsoft SQL Server. Satu codebase dapat dijalankan sebagai node `pusat`, `malang`, atau `surabaya`, dipilih melalui `APP_NODE` dan koneksi database aktif melalui `DB_CONNECTION`.

README ini menjadi panduan onboarding dan serah-terima pekerjaan. Sistem masih dalam pengembangan; khususnya transaksi POS dan sinkronisasi pusat–cabang belum siap digunakan untuk operasional produksi.

## Status implementasi

### Fitur yang sudah tersedia

- Login berbasis username/password dengan role `kasir`, `admin_cabang`, dan seeder `admin_pusat`.
- Route POS dibatasi untuk `kasir` dan `admin_cabang`; menu POS admin cabang tetap berada dalam panel admin.
- Dashboard admin cabang menyesuaikan nama/kode cabang aktif, menampilkan ringkasan stok dan tren omzet/jumlah transaksi 7 hari terakhir.
- Laba belum dihitung karena skema produk/transaksi saat ini belum menyediakan harga modal.
- Manajemen stok cabang menampilkan inventaris, pencarian, filter stok, pagination, dan pencatatan stok masuk.
- Factory tersedia untuk produk dan user; seeder katalog dan user memilih data berdasarkan `APP_NODE`.
- Migrasi tabel bisnis menggunakan koneksi dari `DB_CONNECTION`; migrasi `Stok`, `Pegawai`, `Transaksi`, dan `DetailTransaksi` hanya berjalan untuk node cabang.
- Hamburger sidebar tersedia di dashboard, manajemen stok, dan POS admin cabang.

### Hal yang belum selesai

- Sinkronisasi katalog ke cabang masih berupa prototipe satu arah dan belum menjadi alur operasional teruji.
- Belum ada sinkronisasi transaksi cabang ke pusat, outbox/inbox, retry, idempotensi, atau indikator status sinkronisasi.
- Dashboard admin pusat dan route `/admin-pusat/dashboard` belum tersedia, meski login role `admin_pusat` diarahkan ke URL tersebut.
- Checkout POS masih mengandung asumsi Malang dan perlu dibuat node-aware serta divalidasi di server sebelum produksi. Lihat daftar pekerjaan lanjutan.

## Arsitektur database

Atur satu instalasi aplikasi untuk menunjuk tepat ke satu node/database. `APP_NODE` dan `DB_CONNECTION` harus sesuai; nama koneksi ada di `config/database.php`.

| `APP_NODE` | `DB_CONNECTION` | Database |
| --- | --- | --- |
| `pusat` | `sqlsrv_pusat` | `db_pusat` |
| `malang` | `sqlsrv_malang` | `db_cabang_mlg` |
| `surabaya` | `sqlsrv_surabaya` | `db_cabang_sby` |

### Tabel bisnis

| Node | Tabel bisnis |
| --- | --- |
| Pusat | `Cabang`, `Kategori`, `Produk`, `Supplier`, `RekapTransaksi`, `users` |
| Malang/Surabaya | `Cabang`, `Kategori`, `Produk`, `Pegawai`, `Stok`, `Transaksi`, `DetailTransaksi`, `users` |

Tabel `Stok` hanya dibuat untuk node cabang. Pusat menyimpan katalog dan rekap, bukan jumlah stok fisik cabang. `Cabang`, `Kategori`, dan `Produk` tersedia pada node yang relevan agar aplikasi cabang memiliki salinan katalog lokal.

Laravel juga dapat memiliki tabel internal seperti `migrations`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, dan `failed_jobs`, tergantung migration serta backend session/cache/queue yang digunakan. Tabel internal tersebut bukan bagian dari enam tabel bisnis pusat.

### Seeder demo

`CatalogSeeder` mengisi cabang Malang dan Surabaya di pusat, tetapi hanya cabang lokal pada node cabang. Seeder menambahkan stok hanya pada node cabang. `UserCabangSeeder` membuat akun sesuai node. `TransaksiSeeder` tidak mengisi transaksi contoh di pusat.

Kredensial demo yang dibuat seeder:

| Node | Role | Username | Password awal |
| --- | --- | --- | --- |
| Malang | Kasir | `kasir_mlg` | `password123` |
| Malang | Admin cabang | `admin_mlg` | `password123` |
| Surabaya | Kasir | `kasir_sby` | `password123` |
| Surabaya | Admin cabang | `admin_sby` | `password123` |
| Pusat | Admin pusat | `admin_pusat` | `password123` |

Hanya untuk pengembangan lokal. Ganti password dan terapkan kebijakan kredensial yang aman sebelum deployment; jangan gunakan akun demo untuk produksi.

## Struktur proyek

| Path | Fungsi |
| --- | --- |
| `app/Http/Controllers/AuthController.php` | Login, logout, dan pengalihan berdasarkan role |
| `app/Http/Controllers/PosController.php` | Daftar produk dan proses checkout (masih perlu dibuat node-aware dan diperkuat) |
| `app/Http/Controllers/AdminCabangController.php` | Dashboard admin cabang |
| `app/Http/Controllers/StokController.php` | Daftar inventaris cabang dan stok masuk |
| `app/Services/SyncProductService.php` | Prototipe sinkronisasi katalog pusat ke Malang |
| `app/Models/` | Model dan relasi data |
| `routes/web.php` | Route login, POS/checkout, stok, dashboard cabang, dan logout |
| `resources/views/auth/` | Halaman login |
| `resources/views/pos/` | Antarmuka kasir |
| `resources/views/admin-cabang/` | Dashboard admin cabang |
| `resources/views/stok/` | Manajemen stok cabang |
| `database/migrations/` | Definisi dan perubahan skema database |
| `database/seeders/` | Data contoh awal |
| `config/database.php` | Koneksi SQLite dan SQL Server pusat/cabang |
| `config/app.php` | Pemetaan `APP_NODE` ke identitas cabang aktif |

### Alur transaksi saat ini

1. Kasir atau admin cabang yang sudah login membuka `/pos`.
2. Aplikasi merender produk dan stok yang tersedia pada node aktif.
3. Browser mengirim keranjang dan pembayaran ke `/pos/checkout`.
4. Controller POS saat ini masih memiliki asumsi koneksi/cabang Malang; alur ini belum aman untuk deployment multi-node sebelum diperbaiki.

### Route dan hak akses

| Method | Path | Akses | Keterangan |
| --- | --- | --- | --- |
| `GET` | `/login` | Guest | Form login |
| `POST` | `/login` | Guest | Autentikasi |
| `GET` | `/pos` | `kasir`, `admin_cabang` | POS |
| `POST` | `/pos/checkout` | `kasir`, `admin_cabang` | Checkout |
| `GET` | `/admin-cabang/dashboard` | `admin_cabang` | Dashboard cabang aktif |
| `GET` | `/admin/stok` | `admin_cabang` | Daftar/pencarian/filter stok |
| `POST` | `/admin/stok/masuk` | `admin_cabang` | Tambah stok cabang aktif |
| `POST` | `/logout` | Login | Logout |

Route admin pusat belum tersedia. Route `/up` adalah endpoint health check Laravel.

## Persyaratan Windows

Gunakan Windows 10/11 64-bit. Versi dan extension PHP harus konsisten antara terminal/CLI dan web server.

### Wajib untuk pengembangan

1. **PHP 8.3 atau lebih baru yang masih memenuhi constraint proyek (`^8.3`).** Extension yang dibutuhkan Laravel meliputi `ctype`, `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, dan `zip`.
2. **Microsoft SQL Server PHP drivers:** extension `sqlsrv` dan `pdo_sqlsrv`. Pasang versi yang cocok dengan versi PHP, arsitektur 64-bit, dan mode Thread Safe (TS) atau Non-Thread Safe (NTS) PHP yang digunakan.
3. **Microsoft ODBC Driver for SQL Server**, dibutuhkan oleh extension SQL Server PHP. Pilih versi yang didukung oleh driver PHP yang dipasang.
4. **Composer 2.x** untuk dependency PHP.
5. **Node.js versi LTS yang didukung Vite di proyek ini** beserta npm untuk dependency dan build frontend.
6. **SQL Server** yang dapat diakses oleh aplikasi. Untuk penggunaan beberapa server, aktifkan TCP/IP dan port yang sesuai. Jangan membuka SQL Server ke internet publik.
7. **Git**, jika mengambil source code melalui Git.

### Opsional

- **SQL Server Management Studio (SSMS)** untuk membuat database, menjalankan query, dan memeriksa tabel.
- **Visual Studio Code** atau editor PHP lainnya.
- **Laragon** dapat digunakan sebagai lingkungan lokal Windows, asalkan PHP yang dipilih memiliki extension SQL Server yang sesuai. Pastikan `php` di terminal dan PHP yang dipakai Laragon adalah versi yang sama.

Periksa perangkat lunak dan extension dari PowerShell:

```powershell
php -v
php -m
composer -V
node -v
npm -v
```

Pastikan output `php -m` memuat `pdo_sqlsrv` dan `sqlsrv` jika menggunakan SQL Server. Setelah mengganti file konfigurasi extension PHP, restart terminal, PHP-FPM/Apache, atau Laragon.

## Menyiapkan proyek secara lokal

### 1. Ambil source code dan pasang dependency

Jalankan PowerShell dari folder tempat proyek akan disimpan:

```powershell
git clone <URL-REPOSITORY>
Set-Location .\minimarket-multicabang
composer install
npm install
```

Jika source code sudah ada di komputer, cukup buka folder proyek tersebut dan jalankan perintah install.

### 2. Konfigurasikan `.env`

Buat file lokal dari template:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Atur `.env` sesuai alamat dan kredensial SQL Server yang benar. Contoh berikut menggunakan placeholder—ganti semua nilainya:

```dotenv
APP_NAME="Minimarket Multi-Cabang"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_NODE=malang
DB_CONNECTION=sqlsrv_malang

DB_HOST_PUSAT=alamat-server-pusat
DB_PORT_PUSAT=1433
DB_DATABASE_PUSAT=db_pusat
DB_USERNAME_PUSAT=pengguna_aplikasi
DB_PASSWORD_PUSAT=password-lokal

DB_HOST_SURABAYA=alamat-server-surabaya
DB_PORT_SURABAYA=1433
DB_DATABASE_SURABAYA=db_cabang_sby
DB_USERNAME_SURABAYA=pengguna_aplikasi
DB_PASSWORD_SURABAYA=password-lokal

DB_HOST_MALANG=127.0.0.1
DB_PORT_MALANG=1433
DB_DATABASE_MALANG=db_cabang_mlg
DB_USERNAME_MALANG=pengguna_aplikasi
DB_PASSWORD_MALANG=password-lokal
```

Sesuaikan pasangan `APP_NODE` dan `DB_CONNECTION` untuk setiap instalasi:

| Instalasi | `APP_NODE` | `DB_CONNECTION` |
| --- | --- | --- |
| Pusat | `pusat` | `sqlsrv_pusat` |
| Cabang Malang | `malang` | `sqlsrv_malang` |
| Cabang Surabaya | `surabaya` | `sqlsrv_surabaya` |

Kedua nilai harus mengarah ke database node yang sama. Aplikasi dan migrasi menggunakan koneksi default tersebut; jangan hanya memberi opsi `--database` yang berbeda dengan `DB_CONNECTION`, karena migrasi node juga membaca `APP_NODE` dan dapat mengarah ke koneksi yang salah. Setelah mengubah `.env`, jalankan `php artisan config:clear`.

Jangan mengirim file `.env` ke Git atau membagikan password. Untuk server produksi, gunakan `APP_ENV=production`, `APP_DEBUG=false`, kredensial SQL Server khusus aplikasi dengan hak akses minimum, HTTPS, dan secret yang berbeda dari lingkungan lokal.

### 3. Siapkan SQL Server

- Pastikan SQL Server berjalan dan database yang dituju sudah dibuat.
- Pastikan TCP/IP, firewall, nama host, port, username, dan password cocok dengan `.env`.
- Pastikan `.env` telah memilih pasangan node/koneksi yang benar sebelum migration atau seeder dijalankan.
- Jalankan migration hanya setelah memastikan database dan backup yang benar. Migration mengubah struktur database, bukan menggantikan backup.

```powershell
php artisan config:clear
php artisan migrate:status
php artisan migrate
```

Perintah di atas berlaku untuk node yang dipilih di `.env`. Ulangi pada tiap database dengan mengatur `APP_NODE` dan `DB_CONNECTION` yang cocok. Periksa rencana/skema dan backup sebelum menjalankan migration pada database bersama. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau menghapus tabel pada database berisi data tanpa persetujuan dan backup yang dapat dipulihkan.

### 4. Isi data contoh

```powershell
php artisan db:seed
```

Seeder membaca `APP_NODE`: pusat memasukkan katalog/cabang dan akun admin pusat; cabang memasukkan data cabang lokal, katalog lokal, stok awal, pegawai, akun, serta transaksi demo. Seeder menggunakan `updateOrCreate`/`firstOrCreate` untuk data yang didukung agar dapat dijalankan berulang, tetapi tetap jalankan hanya setelah memastikan node dan database sudah benar.

### 5. Build dan jalankan

Build aset:

```powershell
npm run build
```

Untuk pengembangan, buka dua terminal di folder proyek.

Terminal pertama:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal kedua:

```powershell
npm run dev
```

Buka `http://127.0.0.1:8000`. Halaman `/` mengalihkan pengguna ke `/login`; kasir diarahkan ke `/pos`, admin cabang ke dashboard cabang. Role admin pusat saat ini diarahkan ke `/admin-pusat/dashboard`, tetapi halaman tersebut belum dibuat.

Jalankan test fitur dan unit menggunakan SQLite test yang sudah dikonfigurasi di `phpunit.xml`:

```powershell
php artisan test --compact
```

## Keterbatasan yang perlu dibereskan sebelum produksi

Kondisi berikut terlihat pada implementasi saat ini dan harus diperlakukan sebagai pekerjaan lanjutan, bukan fitur yang sudah tersedia:

- `PosController::store` masih memulai transaksi pada `sqlsrv_malang`, menggunakan ID cabang `MLG`, dan membentuk ID/faktur dengan asumsi Malang. Sesuaikan koneksi dan identitas cabang dengan konfigurasi aktif sebelum menjalankan checkout di Surabaya.
- Checkout menerima detail produk, harga, total, dan jumlah dari browser. Ambil harga dari database di server, hitung ulang total, validasi jumlah/stok, dan kurangi stok secara atomik di dalam transaksi yang sama.
- Relasi `Produk::stok()` belum dibatasi oleh `id_cabang`. Pastikan query stok selalu menggunakan cabang aktif selain ID produk, terutama jika model diperluas untuk membaca lebih dari satu koneksi.
- `SyncProductService` saat ini hanya prototipe pusat-ke-Malang; ia belum mengirim katalog ke Surabaya, belum terhubung ke route/job, dan belum memiliki pengujian integrasi.
- Belum ada pengiriman transaksi ke pusat, outbox/inbox, deduplikasi/idempotensi, retry/backoff, penanganan offline, atau pemantauan status sinkronisasi.
- Dashboard admin pusat belum dibuat meskipun autentikasi admin pusat sudah tersedia.
- Skema belum memiliki harga modal; laba tidak dapat dihitung secara akurat sampai biaya produk dicatat dan sumber perhitungannya ditentukan.
- Session, cache, dan queue default memakai backend database menurut `.env.example`. Migration tabel Laravel pendukung dapat membuat tabel internal selain tabel bisnis; pastikan koneksi/backendnya sesuai kebutuhan deployment.

Selesaikan celah validasi, otorisasi, idempotensi, audit, dan pemulihan data sebelum aplikasi dipakai untuk transaksi riil.

## Prioritas pekerjaan untuk kontributor berikutnya

1. Mulai dari membuat checkout node-aware: gunakan koneksi default dan identitas cabang dari konfigurasi, validasi ulang semua detail di server, lalu kurangi stok dengan kondisi `id_cabang` dan `id_produk` secara atomik.
2. Tambahkan test feature untuk checkout valid, stok tidak cukup, harga/total yang dimanipulasi, dan konfigurasi kedua cabang. Gunakan database test SQLite; jangan menjalankan test terhadap SQL Server bersama.
3. Lengkapi sinkronisasi katalog untuk Malang dan Surabaya, tetapkan cara menjalankannya (command/job/API), dan uji kegagalan/retry. Jangan sinkronkan stok fisik pusat.
4. Buat dashboard dan route admin pusat untuk mengelola master serta membaca `RekapTransaksi`.
5. Setelah kepemilikan data dan kontrak API disepakati, rancang sinkronisasi transaksi dengan outbox/inbox, idempotensi, autentikasi cabang, dan monitoring.

## Rancangan tujuan: pusat terhubung ke banyak cabang

### Pola arsitektur yang disarankan

Gunakan **API pusat dengan sinkronisasi store-and-forward**, bukan membuka port SQL Server pusat untuk koneksi langsung dari semua cabang.

```text
POS cabang + SQL Server lokal
        │ transaksi lokal + outbox
        │ HTTPS, autentikasi per perangkat/cabang
        ▼
API pusat + database pusat
        │ katalog/harga versi terbaru
        ├──────────► Cabang Malang
        └──────────► Cabang Surabaya
```

- **Pusat** menjadi sumber utama data master seperti cabang, produk, kategori, harga, akun, serta laporan gabungan.
- **Cabang** menyimpan transaksi dan stok lokal agar kasir tetap dapat bekerja ketika internet terputus.
- Setiap cabang memiliki `id_cabang` permanen. Penjualan dan perubahan stok selalu membawa identitas cabang tersebut.
- Saat transaksi disimpan lokal, simpan juga event pada tabel **outbox** di transaksi database yang sama. Job terpisah mengirim event ke API pusat ketika koneksi tersedia.
- Pusat memvalidasi dan menyimpan event secara **idempoten** menggunakan ID event unik. Jika cabang mengirim ulang akibat timeout, pusat mengakui event yang sudah diproses tanpa menggandakan penjualan.
- API pusat menyediakan pengiriman perubahan master berdasarkan versi atau cursor. Cabang mencatat versi terakhir yang sudah diterapkan agar dapat melanjutkan unduhan.
- Tetapkan kepemilikan data: pusat mengubah katalog/harga; cabang mencatat transaksi dan stok cabangnya. Jangan menimpa stok cabang dengan snapshot lama dari pusat.
- Pantau jumlah antrian tertunda, waktu sinkronisasi terakhir, kegagalan, dan dead-letter/retry. Sediakan tindakan operator untuk mencoba ulang event yang gagal.

### Tahapan pengembangan

1. **Stabilkan POS satu cabang:** tentukan cabang dari konfigurasi/akun, hitung total di server, validasi produk dan harga, kunci stok saat checkout, dan uji transaksi bersamaan.
2. **Rancang identitas dan skema sinkronisasi:** ID global/UUID transaksi dan event, `id_cabang`, waktu UTC, versi skema, status sync, idempotency key, audit perubahan, serta aturan retensi.
3. **Pisahkan master pusat dan data lokal:** tetapkan tabel mana yang authoritative di pusat dan mana yang dimiliki cabang; siapkan migration bertahap tanpa menghapus data lama.
4. **Bangun API dengan keamanan:** HTTPS, kredensial/token per cabang yang dapat dicabut, validasi payload, otorisasi scope cabang, rate limit, dan pencatatan audit. Jangan menerima `id_cabang` dari klien tanpa memverifikasi identitas cabangnya.
5. **Bangun outbox/inbox dan worker:** buat transaksi lokal dan outbox secara atomik; kirim ulang dengan backoff; pusat melakukan deduplikasi dan memberikan acknowledgement.
6. **Bangun distribusi katalog:** versi katalog/harga, endpoint delta, cursor per cabang, penerapan lokal yang aman, dan kebijakan jika harga berubah saat kasir sedang bertransaksi.
7. **Uji kondisi nyata:** putus internet, timeout setelah pusat menerima event, pengiriman ulang, data duplikat, stok habis bersamaan, versi payload berbeda, pemulihan backup, dan cabang yang lama offline.
8. **Operasionalkan:** deployment terpisah per lingkungan, TLS, backup terjadwal dan uji restore, monitoring, alarm sinkronisasi tertunda, rotasi secret, serta prosedur pemulihan cabang.

Jangan mulai dari replikasi dua arah tanpa aturan konflik. Tentukan lebih dulu data mana yang dimiliki pusat dan cabang. Untuk penjualan, pendekatan event/outbox yang append-only lebih mudah diaudit daripada menyalin dan menimpa baris transaksi.

### Kriteria siap digunakan lintas cabang

- Kasir cabang dapat tetap bertransaksi saat internet ke pusat mati.
- Setiap transaksi dan detail hanya masuk satu kali di pusat walaupun dikirim ulang.
- Pemulihan koneksi mengirim transaksi tertunda tanpa kehilangan atau menggandakan data.
- Harga/katalog pusat dapat diterima cabang beserta versi dan waktu berlakunya.
- Perubahan stok hanya memengaruhi cabang pemilik stok.
- Sistem menunjukkan status sinkronisasi dan menyediakan cara aman untuk menangani kegagalan.
- Hak akses, backup/restore, TLS, dan audit sudah diuji sebelum go-live.

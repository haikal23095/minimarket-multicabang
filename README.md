# Minimarket Multi-Cabang

Aplikasi web POS minimarket berbasis Laravel 13 dan Microsoft SQL Server. Satu codebase dapat dijalankan sebagai node `pusat`, `malang`, atau `surabaya`, dipilih melalui `APP_NODE` dan koneksi database aktif melalui `DB_CONNECTION`.

README ini menjadi panduan menjalankan aplikasi dan acuan penyusunan laporan proyek akhir mata kuliah Basis Data 3 dengan topik basis data terdistribusi. Aplikasi dan rancangan database masih dalam pengembangan; fitur atau objek SQL yang berstatus rencana di bawah ini belum boleh dilaporkan sebagai implementasi selesai.

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

- Aplikasi tidak menyalin katalog antardatabase. Rancangan distribusinya menggunakan SQL Server transactional replication satu arah dari pusat ke cabang. Status publication, subscription, dan replikasi aktual harus diverifikasi pada server SQL Server; README ini bukan bukti bahwa konfigurasi tersebut sudah aktif.
- Belum ada alur integrasi transaksi cabang yang andal ke pusat, outbox/inbox, retry, idempotensi, atau indikator status. Perintah rekap yang ada masih prototipe dan hanya membaca transaksi Malang.
- Dashboard admin pusat dan route `/admin-pusat/dashboard` belum tersedia, meski login role `admin_pusat` diarahkan ke URL tersebut.
- Checkout POS masih mengandung asumsi Malang dan perlu dibuat node-aware serta divalidasi di server sebelum produksi. Lihat daftar pekerjaan lanjutan.
- View, stored procedure, trigger, dan cursor SQL Server untuk proyek akhir masih merupakan pekerjaan yang direncanakan; belum ada skrip `.sql` implementasinya di repository.

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

### Relasi konseptual untuk ERD

Relasi berikut mengikuti foreign key yang dibuat oleh migration aplikasi. Verifikasi lagi pada skema SQL Server setelah seluruh migration diterapkan.

| Entitas induk | Entitas terkait | Kardinalitas/kunci |
| --- | --- | --- |
| `Kategori` | `Produk` | Satu kategori dapat memiliki banyak produk; `Produk.id_kategori` mengacu ke `Kategori.id_kategori`. |
| `Produk` | `Stok` | Satu produk dapat memiliki stok pada beberapa cabang; kunci stok adalah gabungan `id_cabang` dan `id_produk`. |
| `Cabang` | `Stok` | Satu cabang memiliki banyak baris stok; `Stok.id_cabang` mengacu ke `Cabang.id_cabang`. |
| `Cabang` | `Transaksi` | Satu cabang memiliki banyak transaksi; `Transaksi.id_cabang` mengacu ke `Cabang.id_cabang`. |
| `Pegawai` | `Transaksi` | Satu pegawai dapat melayani banyak transaksi; setiap transaksi menyimpan `id_pegawai`. |
| `Transaksi` | `DetailTransaksi` | Satu transaksi memiliki banyak baris detail; setiap detail menyimpan `id_transaksi`. |
| `Produk` | `DetailTransaksi` | Satu produk dapat muncul pada banyak detail transaksi; setiap detail menyimpan `id_produk`. |
| `Cabang` | `RekapTransaksi` | Satu cabang dapat memiliki banyak rekap harian; setiap rekap menyimpan cabang dan tanggal. |

### Topologi dan strategi distribusi data

Topologi yang dirancang menggunakan instance SQL Server terpisah untuk pusat, Malang, dan Surabaya. Distributor dapat berada pada instance pusat atau instance terpisah sesuai konfigurasi dan kebutuhan infrastruktur:

```text
SQL Server pusat (publisher; distributor sesuai konfigurasi)
    ├── transactional replication satu arah ──> SQL Server Malang (subscriber)
    └── transactional replication satu arah ──> SQL Server Surabaya (subscriber)
```

| Data | Sumber utama | Salinan/lokasi | Strategi dan batasan |
| --- | --- | --- | --- |
| `Kategori`, `Produk` | Pusat | Pusat, Malang, Surabaya | Replikasi transactional satu arah pusat ke cabang; perubahan katalog ditulis di pusat. |
| `Stok`, `Transaksi`, `DetailTransaksi`, `Pegawai` | Masing-masing cabang | Database cabang pemilik | Data operasional lokal; jangan menimpa stok cabang dengan data pusat. |
| `RekapTransaksi` | Pusat | Pusat | Ringkasan penjualan per cabang dan tanggal; mekanisme pengiriman saat ini masih prototipe. |

Replikasi transactional SQL Server mengirim perubahan dari publisher ke subscriber melalui agen replikasi; ini bukan pemanggilan service Laravel yang membaca lalu menyalin baris ke database cabang. Dalam rancangan ini katalog bersifat satu arah dan dapat mengalami jeda distribusi. Sebelum menyatakan replikasi aktif, dokumentasikan publisher, distributor, publication, artikel, subscription, jadwal/agen distribusi, serta hasil verifikasi subscriber. Jangan menganggap view, procedure, trigger, atau objek database lain otomatis ikut direplikasi hanya karena tabel publikasi sudah dipilih; tentukan dan uji deployment objek tersebut secara terpisah.

### Rancangan objek SQL Server untuk proyek akhir

Objek berikut adalah **target implementasi**, bukan fitur yang sudah tersedia. Tentukan database tempat objek dibuat, pemilik data, izin eksekusi, dan apakah objek lokal atau perlu dipasang pada lebih dari satu instance. Skrip dapat dikelompokkan di `database/sqlserver/` setelah mulai dibuat.

| Objek | Rancangan fungsi | Lokasi awal yang disarankan |
| --- | --- | --- |
| View `dbo.vw_StokProdukCabang` | Menyajikan stok lokal beserta nama produk dan kategori untuk laporan inventaris; gunakan `id_cabang` dan `id_produk` sebagai kunci join. | Database cabang, setelah artikel katalog tersedia. |
| View `dbo.vw_RekapPenjualanPusat` | Menyajikan rekap pusat dengan nama cabang dan tanggal agar dapat digunakan sebagai laporan lintas cabang. | Database pusat. |
| Stored procedure `dbo.usp_CatatStokMasuk` | Memvalidasi cabang, produk, dan kuantitas positif, lalu menambah stok yang sudah ada atau membuat baris stok baru dalam transaksi database lokal. | Database cabang; jalankan pada cabang yang memiliki stok. |
| Stored procedure `dbo.usp_LaporanPenjualanPeriode` | Mengambil rekap penjualan pusat berdasarkan parameter periode dan, bila diperlukan, cabang. | Database pusat. |
| Trigger `dbo.trg_DetailTransaksi_UpdateStok` | Mengurangi stok lokal ketika detail transaksi ditambahkan, dengan validasi stok cukup. Harus berbasis kumpulan baris `inserted`, karena satu statement dapat memasukkan beberapa detail. | Database cabang. |
| Cursor demonstrasi laporan stok minimum | Mengiterasi hasil stok di bawah ambang untuk menunjukkan pemrosesan baris demi baris; bandingkan hasil dan biaya dengan query set-based. Bukan mekanisme utama transaksi atau sinkronisasi. | Skrip/lab database cabang. |

Nama-nama di atas merupakan usulan yang boleh disesuaikan dan belum menjamin kesesuaian dengan rubrik dosen. Untuk trigger pengurang stok, pilih satu pemilik logika: jika trigger yang mengubah stok, hapus atau nonaktifkan pengurangan stok yang sama di aplikasi agar stok tidak berkurang dua kali. Uji rollback ketika stok tidak cukup dan input multi-baris. Jangan memakai cursor di dalam trigger; operasi tabel sebaiknya set-based. Batasi cursor pada demonstrasi yang memang membutuhkan urutan pemrosesan dan jelaskan alternatif set-based serta trade-off performanya.

### Rencana bukti implementasi

Sebelum laporan menyatakan objek selesai, siapkan bukti berikut:

1. Diagram topologi tiga instance dan diagram ERD yang sesuai skema aktual.
2. Skrip pembuatan objek SQL Server, parameter/kolom yang dipakai, prasyarat, dan urutan eksekusi per database.
3. Hasil uji insert/update produk di pusat dan bukti produk/kategori diterima di kedua subscriber; sertakan waktu atau jeda replikasi.
4. Hasil uji view dengan data contoh yang bisa ditelusuri kembali ke tabel sumber.
5. Hasil uji stored procedure untuk input valid dan tidak valid.
6. Hasil uji trigger untuk transaksi normal, stok tidak cukup, input beberapa baris, dan rollback.
7. Hasil cursor untuk data yang sama dengan pembanding set-based, disertai penjelasan kapan cursor layak digunakan.
8. Bukti status agen replikasi dan penanganan kegagalan/keterlambatan. Samarkan alamat server, username, password, token, dan data sensitif pada tangkapan layar.

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
- Replikasi SQL Server native untuk `Kategori` dan `Produk` perlu diverifikasi pada publisher/distributor/subscriber pusat, Malang, dan Surabaya; Laravel tidak melakukan penyalinan katalog.
- Pengiriman rekap saat ini merupakan prototipe yang membaca transaksi Malang dari aplikasi; ini bukan replikasi database dan belum mencakup transaksi Surabaya, outbox/inbox, deduplikasi/idempotensi, retry/backoff, penanganan offline, atau pemantauan status.
- Dashboard admin pusat belum dibuat meskipun autentikasi admin pusat sudah tersedia.
- Skema belum memiliki harga modal; laba tidak dapat dihitung secara akurat sampai biaya produk dicatat dan sumber perhitungannya ditentukan.
- Session, cache, dan queue default memakai backend database menurut `.env.example`. Migration tabel Laravel pendukung dapat membuat tabel internal selain tabel bisnis; pastikan koneksi/backendnya sesuai kebutuhan deployment.
- View, stored procedure, trigger, dan cursor yang dirancang untuk laporan proyek akhir belum diimplementasikan atau diuji.

Selesaikan celah validasi, otorisasi, idempotensi, audit, dan pemulihan data sebelum aplikasi dipakai untuk transaksi riil.

## Prioritas pekerjaan untuk kontributor berikutnya

1. Mulai dari membuat checkout node-aware: gunakan koneksi default dan identitas cabang dari konfigurasi, validasi ulang semua detail di server, lalu kurangi stok dengan kondisi `id_cabang` dan `id_produk` secara atomik.
2. Tambahkan test feature untuk checkout valid, stok tidak cukup, harga/total yang dimanipulasi, dan konfigurasi kedua cabang. Gunakan database test SQLite; jangan menjalankan test terhadap SQL Server bersama.
3. Verifikasi SQL Server transactional replication satu arah untuk `Kategori` dan `Produk` dari pusat ke Malang dan Surabaya, termasuk status Log Reader/Distribution Agent dan pemulihan subscriber. Jangan replikasi stok fisik pusat.
4. Buat dashboard dan route admin pusat untuk mengelola master serta membaca `RekapTransaksi`.
5. Rancang objek view, stored procedure, trigger, dan cursor sesuai tujuan, uji pada database SQL Server, dan dokumentasikan dampaknya terhadap alur aplikasi.
6. Setelah kepemilikan data dan mekanisme integrasi transaksi disepakati, rancang pengiriman transaksi dengan outbox/inbox, idempotensi, autentikasi cabang, dan monitoring.

## Rancangan tujuan: pusat terhubung ke banyak cabang

### Pola arsitektur yang disarankan

Untuk **katalog**, gunakan SQL Server transactional replication native satu arah; Laravel menulis perubahan hanya ke publisher pusat dan tidak menyalin baris ke database cabang. View dan stored procedure laporan ditempatkan pada database pemilik data. Trigger transaksi/stok dipasang pada database cabang yang memiliki tabel terkait. Untuk **integrasi transaksi lintas cabang**, mekanismenya belum ditetapkan pada implementasi saat ini; jelaskan pilihan dan batasannya sebagai pekerjaan lanjutan, bukan fitur yang sudah selesai.

```text
Admin pusat / Laravel
        │ tulis Kategori dan Produk
        ▼
SQL Server Publisher (Distributor sesuai konfigurasi)
        ├── transactional replication ──► SQL Server db_cabang_mlg
        └── transactional replication ──► SQL Server db_cabang_sby

Transaksi dan stok cabang ──► database SQL Server cabang masing-masing
```

- **Pusat** menjadi sumber utama data master seperti cabang, produk, kategori, dan harga; tabel `Kategori` dan `Produk` didistribusikan dengan SQL Server transactional replication.
- **Cabang** menyimpan transaksi dan stok lokal agar kasir tetap dapat bekerja ketika internet terputus.
- Setiap cabang memiliki `id_cabang` permanen. Penjualan dan perubahan stok selalu membawa identitas cabang tersebut.
- Distribusi native katalog harus menjaga arah satu arah pusat-ke-cabang; stok cabang tidak boleh direplikasi sebagai master pusat.
- Tetapkan kepemilikan data: pusat mengubah katalog/harga; cabang mencatat transaksi dan stok cabangnya. Jangan menimpa stok cabang dengan snapshot lama dari pusat.
- Untuk transaksi lintas node, tentukan secara eksplisit mekanisme konsolidasi sebelum implementasi; jangan mengasumsikan replikasi katalog juga mengirim transaksi cabang.

### Tahapan pengembangan

1. **Stabilkan POS satu cabang:** tentukan cabang dari konfigurasi/akun, hitung total di server, validasi produk dan harga, kunci stok saat checkout, dan uji transaksi bersamaan.
2. **Rancang identitas dan skema sinkronisasi:** ID global/UUID transaksi dan event, `id_cabang`, waktu UTC, versi skema, status sync, idempotency key, audit perubahan, serta aturan retensi.
3. **Pisahkan master pusat dan data lokal:** tetapkan tabel mana yang authoritative di pusat dan mana yang dimiliki cabang; siapkan migration bertahap tanpa menghapus data lama.
4. **Rancang konsolidasi transaksi lintas cabang:** tentukan mekanisme, autentikasi/otorisasi cabang, validasi, idempotensi, dan audit sebelum memilih API, replikasi, atau pendekatan lain.
5. **Implementasikan objek SQL Server proyek akhir:** view untuk laporan, stored procedure untuk operasi tervalidasi, trigger set-based untuk aturan stok yang dipilih, dan cursor demonstrasi yang dibandingkan dengan pendekatan set-based.
6. **Operasionalkan replikasi katalog SQL Server:** sertakan artikel `Kategori` dan `Produk`, validasi subscriber Malang/Surabaya, pemantauan latency, dan kebijakan jika harga berubah saat kasir sedang bertransaksi.
7. **Uji kondisi nyata:** perubahan katalog, subscriber terputus, keterlambatan agen replikasi, stok tidak cukup, transaksi multi-baris, rollback, pemulihan backup, dan cabang yang lama offline.
8. **Operasionalkan:** deployment terpisah per lingkungan, keamanan koneksi, backup terjadwal dan uji restore, monitoring, alarm replikasi tertunda, rotasi secret, serta prosedur pemulihan cabang.

Jangan mulai dari replikasi dua arah tanpa aturan konflik. Tentukan lebih dulu data mana yang dimiliki pusat dan cabang. Jika transaksi kelak dikonsolidasikan, pertimbangkan pola event/outbox append-only agar pengiriman ulang dapat diaudit; mekanisme tersebut belum menjadi bagian dari implementasi katalog saat ini.

### Kriteria siap digunakan lintas cabang

- Kasir cabang dapat tetap bertransaksi saat internet ke pusat mati.
- Setiap transaksi dan detail hanya masuk satu kali di pusat walaupun dikirim ulang.
- Pemulihan koneksi mengirim transaksi tertunda tanpa kehilangan atau menggandakan data.
- Harga/katalog pusat dapat diterima cabang beserta versi dan waktu berlakunya.
- Perubahan stok hanya memengaruhi cabang pemilik stok.
- Sistem menunjukkan status sinkronisasi dan menyediakan cara aman untuk menangani kegagalan.
- Hak akses, backup/restore, TLS, dan audit sudah diuji sebelum go-live.

## Panduan laporan proyek akhir Basis Data 3

Gunakan bagian ini sebagai kerangka laporan tentang **basis data terdistribusi minimarket multi-cabang**. Selaraskan isi laporan dengan implementasi dan hasil uji yang benar-benar dilakukan; tandai hal yang belum selesai sebagai batasan atau rencana lanjutan.

### Rumusan tujuan dan ruang lingkup

- **Masalah:** katalog, transaksi, dan stok perlu dikelola pada beberapa lokasi dengan kebutuhan ketersediaan lokal, konsistensi, dan laporan lintas cabang.
- **Tujuan:** merancang dan menguji distribusi katalog dari pusat ke cabang dengan SQL Server transactional replication serta mengevaluasi objek SQL Server untuk operasi dan pelaporan.
- **Ruang lingkup distribusi:** `Kategori` dan `Produk` menjadi data master pusat yang direplikasi satu arah ke Malang dan Surabaya. `Stok`, `Transaksi`, dan `DetailTransaksi` tetap dimiliki database cabang masing-masing.
- **Objek database yang menjadi target praktik:** view, stored procedure, trigger, dan cursor. Cantumkan definisi, lokasi, alasan pemilihan, hasil uji, dan keterbatasan masing-masing.
- **Di luar cakupan kecuali benar-benar diimplementasikan:** replikasi dua arah, sinkronisasi transaksi yang tahan offline, konsistensi stok global real-time, serta kesiapan produksi.

### Pertanyaan analisis yang perlu dijawab

1. Mengapa katalog direplikasi satu arah, sementara stok dan transaksi tetap lokal?
2. Siapa pemilik dan pihak yang berwenang mengubah setiap tabel?
3. Bagaimana perubahan katalog dikirim, bagaimana jeda diukur, dan bagaimana subscriber dipulihkan?
4. Bagaimana view dan stored procedure mengurangi duplikasi query atau menjaga validasi operasi?
5. Bagaimana trigger menjaga stok tanpa menyebabkan pengurangan ganda?
6. Apa perbedaan hasil dan biaya pemrosesan cursor dibanding query set-based?
7. Apa dampak gangguan jaringan terhadap operasi cabang dan laporan pusat?

### Struktur laporan yang disarankan

1. **Halaman awal:** judul, identitas tim, mata kuliah Basis Data 3, dosen, program studi, dan tahun akademik.
2. **Abstrak:** masalah, metode distribusi, objek SQL yang benar-benar dibuat, hasil pengujian utama, dan kesimpulan.
3. **Pendahuluan:** latar belakang, identifikasi masalah, rumusan masalah, tujuan, manfaat, batasan, serta sistematika penulisan.
4. **Landasan teori:** basis data terdistribusi, transparansi distribusi, replikasi, fragmentasi/kepemilikan data, konsistensi, SQL Server transactional replication, view, stored procedure, trigger, cursor, dan transaksi database.
5. **Analisis dan perancangan:** kebutuhan fungsional/nonfungsional, ERD, kamus data, topologi jaringan/database, matriks penempatan tabel, arah aliran data, dan hak akses.
6. **Implementasi:** konfigurasi publication/subscription, struktur tabel dan constraint, skrip objek SQL, integrasi aplikasi seperlunya, serta urutan deployment.
7. **Pengujian dan hasil:** skenario, data uji, langkah, hasil yang diharapkan/aktual, bukti SSMS, jeda replikasi, kasus gagal, dan analisis hasil.
8. **Kesimpulan dan saran:** jawaban atas rumusan masalah, batasan hasil, dan prioritas pengembangan berikutnya.
9. **Daftar pustaka dan lampiran:** dokumentasi resmi SQL Server yang digunakan, skrip final, konfigurasi yang sudah disamarkan, diagram, serta bukti pengujian.

### Matriks skenario pengujian minimal

| Area | Skenario | Bukti yang dicatat |
| --- | --- | --- |
| Replikasi katalog | Tambah dan ubah kategori/produk di pusat | Baris sumber dan subscriber, waktu perubahan, serta jeda sampai kedua cabang menerima data. |
| Gangguan replikasi | Subscriber atau agen distribusi terhenti sementara lalu dipulihkan | Status agen, antrean/latency, dan konfirmasi perubahan diterapkan setelah pemulihan. |
| View | Jalankan laporan stok lokal dan rekap pusat dengan rentang/per cabang yang diketahui | Hasil view dibandingkan dengan query tabel dasar. |
| Stored procedure | Input valid, kuantitas nol/negatif, produk tidak dikenal, dan stok yang belum ada | Output/error yang diharapkan dan bukti database tidak berubah pada input gagal. |
| Trigger | Tambahkan detail transaksi satu baris dan beberapa baris; uji stok cukup/tidak cukup | Jumlah stok sebelum/sesudah dan bukti rollback jika operasi ditolak. |
| Cursor | Jalankan laporan stok minimum menggunakan cursor dan pembanding set-based pada data sama | Kesamaan hasil, waktu/biaya bila diukur, dan analisis keterbacaan/performa. |
| Integritas | Uji primary key, foreign key, transaksi, serta data lintas cabang | Hasil query verifikasi dan penjelasan constraint yang mencegah data tidak valid. |

Gunakan database uji, data sintetis, dan backup sebelum percobaan yang mengubah data. Jangan melakukan `migrate:fresh`, menghapus publication/subscription, atau mengubah database yang berisi data penting tanpa persetujuan dan rencana pemulihan.

### Kriteria kualitas laporan

- Diagram dan penjelasan konsisten dengan tiga instance SQL Server yang benar-benar diuji.
- Matriks distribusi menyebut tabel, lokasi sumber, salinan, arah replikasi, dan kewenangan tulis.
- Objek SQL diberi label **diimplementasikan**, **diuji**, atau **rencana** secara jujur.
- Setiap klaim hasil didukung query, output, log agen, atau tangkapan layar yang aman dibagikan.
- Analisis membahas konsistensi, latency, kegagalan koneksi, keamanan, integritas data, serta trade-off cursor terhadap operasi set-based.
- Password, connection string, IP privat sensitif, dan data pribadi disamarkan dari laporan dan lampiran.

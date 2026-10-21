# Minimarket Multi-Cabang

Aplikasi web kasir (POS) minimarket berbasis Laravel. Repository ini saat ini berisi fondasi login, katalog produk, stok, dan pencatatan transaksi untuk cabang Malang. Konfigurasi koneksi database pusat dan cabang lain sudah tersedia, tetapi sinkronisasi pusat–cabang belum diimplementasikan.

Dokumen ini menjelaskan kondisi aplikasi saat ini, cara menjalankannya di Windows, dan tahapan yang disarankan untuk mencapai sistem multi-cabang yang dapat bekerja saat koneksi terputus dan menyinkronkan data kembali.

## Ringkasan fitur saat ini

- Login menggunakan username dan password, dengan role `kasir` dan `admin_cabang`.
- Kasir dapat melihat produk, membuat keranjang, memasukkan pembayaran, dan mengirim transaksi.
- Checkout menyimpan header transaksi dan detail barang serta mengurangi stok.
- Model bisnis yang tersedia: `Cabang`, `Kategori`, `Produk`, `Stok`, `Pegawai`, `User`, `Transaksi`, dan `DetailTransaksi`.
- Aplikasi menggunakan SQL Server untuk model bisnis. Konfigurasi memiliki koneksi `sqlsrv_pusat`, `sqlsrv_surabaya`, dan `sqlsrv_malang`; semua model saat ini menunjuk langsung ke `sqlsrv_malang`.
- Terdapat seed data contoh untuk cabang Malang, produk, kategori, pegawai, dan akun.

## Struktur proyek

| Path | Fungsi |
| --- | --- |
| `app/Http/Controllers/AuthController.php` | Login, logout, dan pengalihan berdasarkan role |
| `app/Http/Controllers/PosController.php` | Daftar produk dan proses checkout |
| `app/Models/` | Model dan relasi data |
| `routes/web.php` | Route halaman login, POS, checkout, dan logout |
| `resources/views/auth/` | Halaman login |
| `resources/views/pos/` | Antarmuka kasir |
| `database/migrations/` | Definisi dan perubahan skema database |
| `database/seeders/` | Data contoh awal |
| `config/database.php` | Koneksi SQLite, SQL Server pusat, serta SQL Server cabang |

### Alur transaksi saat ini

1. Pengguna yang sudah login membuka `/pos`.
2. Aplikasi mengambil produk, kategori, dan stok dari database Malang.
3. Browser mengirim keranjang, total, dan pembayaran ke `/pos/checkout`.
4. Aplikasi menyimpan transaksi serta detail dan mengurangi stok dalam transaksi database.

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

`DB_CONNECTION=sqlsrv_malang` penting untuk menjalankan aplikasi cabang Malang: model aplikasi sudah menetapkan koneksi tersebut secara langsung, sedangkan beberapa komponen Laravel seperti session, queue, dan migration bawaan menggunakan koneksi default. Jika ingin memakai SQLite untuk test atau eksperimen, konfigurasi koneksi model juga perlu disesuaikan—mengubah `DB_CONNECTION` saja tidak mengalihkan model-model tersebut.

Jangan mengirim file `.env` ke Git atau membagikan password. Untuk server produksi, gunakan `APP_ENV=production`, `APP_DEBUG=false`, kredensial SQL Server khusus aplikasi dengan hak akses minimum, HTTPS, dan secret yang berbeda dari lingkungan lokal.

### 3. Siapkan SQL Server

- Pastikan SQL Server berjalan dan database yang dituju sudah dibuat.
- Pastikan TCP/IP, firewall, nama host, port, username, dan password cocok dengan `.env`.
- Untuk pengembangan satu cabang, fokuskan koneksi pada database Malang. Nama database pusat dan Surabaya pada konfigurasi belum berarti data sudah tersambung atau tersinkronisasi.
- Jalankan migration hanya setelah memastikan database dan backup yang benar. Migration mengubah struktur database, bukan menggantikan backup.

```powershell
php artisan migrate:status --database=sqlsrv_malang
php artisan migrate --database=sqlsrv_malang
```

Jika migration terhenti karena tabel atau kolom lama berbeda, periksa skema aktual terlebih dahulu. Jangan langsung menjalankan `migrate:fresh`, `db:wipe`, atau menghapus database—perintah tersebut dapat menghilangkan data.

### 4. Isi data contoh

```powershell
php artisan db:seed --database=sqlsrv_malang
```

Seeder membuat contoh cabang Malang, kategori, produk, stok awal, pegawai, dan akun. Nilai login contoh saat ini:

| Role | Username | Password awal |
| --- | --- | --- |
| Kasir | `kasir_mlg` | `password123` |
| Admin cabang | `admin_mlg` | `password123` |

Akun tersebut hanya untuk pengembangan lokal. Ganti password sebelum pemakaian nyata dan jangan gunakan akun seed sebagai akun produksi.

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

Buka `http://127.0.0.1:8000`. Halaman `/` mengalihkan pengguna ke `/login`; kasir yang berhasil login diarahkan ke `/pos`.

Jalankan test:

```powershell
php artisan test
```

## Keterbatasan yang perlu dibereskan sebelum produksi

Kondisi berikut terlihat pada implementasi saat ini dan harus diperlakukan sebagai pekerjaan lanjutan, bukan fitur yang sudah tersedia:

- Seluruh model dan checkout masih menggunakan koneksi Malang; `id_cabang` checkout juga masih ditetapkan ke `MLG`.
- Relasi stok produk belum memilih cabang. Jika produk yang sama ada pada beberapa cabang, pembacaan atau pengurangan stok perlu selalu memakai pasangan `id_cabang` dan `id_produk`.
- Form checkout mengirim harga dan total dari browser. Server perlu mengambil harga dari database, menghitung ulang total, memvalidasi jumlah dan stok, lalu mengurangi stok secara atomik agar nilai yang dimanipulasi atau checkout bersamaan tidak menyebabkan stok/total keliru.
- Login `admin_cabang` diarahkan ke `/dashboard-cabang`, tetapi route/dashboard tersebut belum tersedia di route saat ini.
- Belum ada API antar-cabang, outbox transaksi, job sinkronisasi, penanda status sinkronisasi, atau penanganan koneksi terputus.
- Koneksi pusat dan cabang lain baru berupa konfigurasi; belum digunakan oleh model atau proses sinkronisasi.
- Session dan queue bawaan dikonfigurasi menggunakan database. Pastikan migration tabel pendukungnya dijalankan pada koneksi yang sesuai, atau pilih backend session/queue yang memang disediakan dan dipantau.

Selesaikan celah validasi, otorisasi, idempotensi, audit, dan pemulihan data sebelum aplikasi dipakai untuk transaksi riil.

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

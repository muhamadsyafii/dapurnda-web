<div align="center">

<img src="public/assets/images/logo-dapurnda.png" alt="Logo DapurNda" width="320">

# DapurNda

### Temukan resep, masak dengan percaya diri, dan bagikan rasa dari dapur sendiri.

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![UI](https://img.shields.io/badge/UI-Responsive-EF4338)](#fitur-utama)
[![Admin](https://img.shields.io/badge/Admin-Sneat-696CFF)](#area-pengelola)

**DapurNda** adalah aplikasi resep berbasis PHP native dan MySQL yang dirancang untuk penggunaan publik, terutama melalui perangkat seluler. Aplikasi ini menyediakan pencarian resep, tutorial memasak bertahap, pengiriman resep oleh pengunjung, moderasi oleh pengelola, statistik tayangan, serta metadata berbagi untuk WhatsApp, Telegram, Facebook, dan platform sosial lainnya.

[Mulai di localhost](#menjalankan-di-localhost) · [Pasang di cPanel](#deployment-ke-cpanel) · [Konfigurasi](#konfigurasi-aplikasi) · [Pemecahan masalah](#pemecahan-masalah)

</div>

---

## Daftar isi

- [Tentang aplikasi](#tentang-aplikasi)
- [Fitur utama](#fitur-utama)
- [Area pengelola](#area-pengelola)
- [Teknologi](#teknologi)
- [Struktur proyek](#struktur-proyek)
- [Persyaratan sistem](#persyaratan-sistem)
- [Menjalankan di localhost](#menjalankan-di-localhost)
- [Instalasi database](#instalasi-database)
- [Konfigurasi aplikasi](#konfigurasi-aplikasi)
- [Akun pengelola awal](#akun-pengelola-awal)
- [Deployment ke cPanel](#deployment-ke-cpanel)
- [Alur kerja aplikasi](#alur-kerja-aplikasi)
- [Keamanan](#keamanan)
- [SEO dan berbagi ke media sosial](#seo-dan-berbagi-ke-media-sosial)
- [Pengelolaan gambar](#pengelolaan-gambar)
- [Panduan pengembangan](#panduan-pengembangan)
- [Pemecahan masalah](#pemecahan-masalah)
- [Kredit dan lisensi](#kredit-dan-lisensi)

## Tentang aplikasi

DapurNda dibuat sebagai website resep yang mudah dipakai oleh pengguna umum, termasuk pengguna yang baru belajar memasak. Antarmuka publik dan area pengelola dipisahkan agar masing-masing mempunyai pengalaman yang sesuai:

- **Website publik** menggunakan desain khas DapurNda yang ringan, hangat, responsif, dan berfokus pada resep.
- **Area pengelola** menggunakan Sneat Free Bootstrap HTML Admin Template yang telah disesuaikan dengan identitas visual DapurNda.
- **Kode privat** seperti konfigurasi, akses database, dan logika bisnis dapat ditempatkan di luar `public_html` saat dipasang di hosting.
- **Tidak memerlukan framework PHP, Composer, atau proses build Node.js** untuk menjalankan aplikasi.

Arsitektur sederhananya:

```text
Browser pengunjung
       │
       ▼
public/*.php ────── public/assets dan public/uploads
       │
       ▼
src/bootstrap.php ── config/config.php
       │
       ▼
     MySQL
```

## Fitur utama

### Untuk pengunjung

- Beranda responsif dengan **6 resep terpopuler berdasarkan jumlah tayangan**.
- Halaman semua resep dengan pencarian nama resep atau bahan.
- Filter kategori serta pilihan tampilan **grid** dan **list**.
- Pemuatan resep bertahap pada perangkat seluler agar halaman tetap nyaman digunakan.
- Halaman detail berisi foto, deskripsi, waktu memasak, jumlah porsi, tingkat kesulitan, bahan, dan langkah memasak.
- Penanda bahan dan langkah yang sudah selesai agar pengguna mudah mengikuti proses memasak.
- Tombol berbagi dengan ikon untuk WhatsApp, Telegram, Facebook, fitur berbagi bawaan perangkat, dan salin tautan.
- Metadata Open Graph, Twitter Card, canonical URL, dan Recipe JSON-LD.
- Tampilan jumlah tayangan pada resep.
- Desain mobile-first untuk ponsel dan tablet tanpa scroll horizontal.

### Untuk kontributor resep

- Form pengiriman resep bertahap agar lebih mudah diisi melalui ponsel.
- Bahan dan langkah memasak dapat ditambahkan secara dinamis.
- Foto asli dapat dipilih tanpa harus dikompres manual terlebih dahulu.
- Validasi persetujuan publikasi dan data kontak kontributor.
- Resep baru masuk sebagai **menunggu moderasi** dan tidak langsung tampil ke publik.
- Pembatasan pengiriman untuk mengurangi spam.

### Konten dan pemasaran

- Kategori resep yang dapat dikelola melalui database.
- Statistik resep populer berdasarkan jumlah tayangan.
- Halaman informasi, privasi, syarat penggunaan, robots, dan sitemap.
- Modul sponsor tetap tersedia di area pengelola, tetapi tampilan sponsor publik dinonaktifkan secara bawaan sampai siap digunakan.

## Area pengelola

Area pengelola tersedia pada `/admin/` dan memakai antarmuka Sneat yang telah disesuaikan untuk desktop, tablet, serta ponsel.

### Dashboard

- Ringkasan jumlah resep, status moderasi, total tayangan, dan data penting lain.
- Grafik aktivitas tayangan resep.
- Peringkat resep yang paling sering dibuka.
- Navigasi samping yang dapat diciutkan pada desktop dan dibuka sebagai menu pada perangkat seluler.

### Pengelolaan resep

- Melihat seluruh resep beserta status dan jumlah tayangannya.
- Meninjau data kontributor serta waktu persetujuan.
- Mengubah isi resep.
- Menyetujui resep agar tayang.
- Menolak resep yang tidak sesuai.
- Memisahkan status **menunggu**, **tayang**, dan **ditolak**.

### Pengelolaan sponsor

- Daftar sponsor dalam tampilan tabel/list.
- Penambahan sponsor melalui modal.
- Pengaturan paket, posisi, periode tayang, tautan, status, impresi, dan klik.
- Daftar permintaan kerja sama sponsor.

### Pengelolaan pengguna

Terdapat dua peran pengelola:

| Peran | Akses |
|---|---|
| **Admin** | Dashboard, moderasi dan pengelolaan resep, sponsor, serta permintaan sponsor. |
| **Super Admin** | Seluruh akses Admin ditambah menu Pengguna untuk menambah, mengubah, mengaktifkan, atau menonaktifkan akun pengelola. |

Password akun pengelola minimal **6 karakter**. Untuk keamanan produksi, tetap gunakan password panjang dan unik.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP native 8.1+ |
| Database | MySQL/MariaDB melalui PDO |
| Website publik | HTML5, CSS3, JavaScript native |
| Dashboard | Sneat Free Bootstrap HTML Admin Template |
| Ikon admin | Boxicons/Sneat icon assets |
| Pemrosesan gambar | PHP GD, Fileinfo, dan validasi MIME |
| Server | Apache dengan `.htaccess` |
| Metadata | Open Graph, Twitter Card, JSON-LD, sitemap, robots |

## Struktur proyek

```text
dapurnda/
├── config/
│   ├── config.php                 # Konfigurasi yang dibaca aplikasi
│   └── config.example.php         # Contoh nilai environment produksi
├── database/
│   ├── dapurnda-phpmyadmin-full.sql
│   ├── schema.sql
│   ├── sample-recipes.sql
│   ├── migrate-2026-11-13.sql
│   └── migrate-2026-11-13-admin-roles.sql
├── public/
│   ├── admin/                     # Login dan halaman pengelola
│   ├── api/                       # Endpoint pencarian
│   ├── assets/                    # CSS, JavaScript, logo, ikon, dan foto contoh
│   ├── uploads/                   # Foto yang diunggah pengguna
│   ├── index.php                  # Beranda
│   ├── search.php                 # Semua resep dan pencarian
│   ├── recipe.php                 # Detail dan tutorial resep
│   ├── add.php                    # Form kontribusi resep
│   ├── go.php                     # Pengarah tautan dan pencatat klik sponsor
│   ├── sitemap.php
│   ├── robots.php
│   └── .htaccess
├── src/
│   ├── bootstrap.php              # Inisialisasi aplikasi
│   ├── db.php                     # Koneksi PDO
│   ├── auth.php                   # Autentikasi dan peran admin
│   ├── helpers.php                # Fungsi bantuan umum
│   ├── layout.php                 # Layout website publik
│   ├── admin_layout.php           # Layout area pengelola
│   ├── recipes.php                # Logika resep dan statistik
│   ├── sponsors.php               # Logika sponsor
│   └── upload.php                 # Validasi dan kompresi foto
├── storage/
│   └── logs/                      # Log aplikasi
├── .gitignore
├── .htaccess                      # Proteksi proyek jika root ikut terpapar
├── index.php                      # Pengarah ke folder public saat lokal
└── README.md
```

> Pada hosting produksi, isi `public/` sebaiknya dipisahkan dari kode privat. Lihat [Deployment ke cPanel](#deployment-ke-cpanel).

## Persyaratan sistem

Pastikan lingkungan memiliki:

- PHP **8.1 atau lebih baru**.
- MySQL atau MariaDB dengan dukungan InnoDB dan `utf8mb4`.
- Apache Web Server.
- Ekstensi PHP `pdo_mysql`, `mbstring`, `fileinfo`, dan `gd`.
- Modul Apache `mod_rewrite`.
- Modul `mod_headers` direkomendasikan untuk header keamanan.
- Hak tulis untuk `public/uploads/` dan `storage/logs/`.

Untuk produksi, HTTPS wajib digunakan agar login, tautan berbagi, gambar Open Graph, dan metadata sosial bekerja dengan alamat publik yang aman.

## Menjalankan di localhost

Contoh berikut mengasumsikan folder proyek bernama `dapurnda` di dalam document root Apache, misalnya `htdocs`, `www`, atau `/var/www/html`.

### 1. Siapkan proyek

Jika menggunakan Git:

```bash
git clone <alamat-private-repository> dapurnda
cd dapurnda
```

Atau ekstrak arsip proyek ke:

```text
htdocs/dapurnda/
```

### 2. Buat database melalui phpMyAdmin

1. Buka `http://localhost/phpmyadmin`.
2. Pilih **New / Baru**.
3. Buat database, misalnya `resep`.
4. Gunakan collation `utf8mb4_unicode_ci` jika tersedia.
5. Buka tab **Import**.
6. Pilih salah satu metode instalasi pada bagian [Instalasi database](#instalasi-database).

Untuk instalasi baru yang langsung siap dicoba, impor:

```text
database/dapurnda-phpmyadmin-full.sql
```

### 3. Atur koneksi database

Konfigurasi lokal bawaan pada `config/config.php` adalah:

```text
Host     : 127.0.0.1
Port     : 3306
Database : resep
Username : root
Password : kosong
```

Jika konfigurasi MySQL lokal berbeda, ubah nilai pada bagian `database` atau sediakan environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, dan `DB_PASS`.

### 4. Pastikan folder dapat ditulis

Pada Linux:

```bash
chmod 755 public/uploads storage/logs
```

Jika Apache belum dapat menulis, gunakan grup user web server atau naikkan menjadi `775`. Jangan gunakan `777` kecuali benar-benar diperlukan untuk diagnosis sementara.

### 5. Buka aplikasi

Jika seluruh proyek berada di bawah document root:

```text
Website : http://localhost/dapurnda/public/
Admin   : http://localhost/dapurnda/public/admin/
```

Root `http://localhost/dapurnda/` juga menyediakan pengarah ke folder `public/`.

> Preview WhatsApp, Telegram, dan layanan sosial lain tidak dapat mengambil metadata dari `localhost`. Gunakan domain HTTPS publik saat menguji social preview.

## Instalasi database

Jangan mengimpor seluruh file SQL sekaligus. Pilih skenario yang sesuai agar data contoh dan struktur tidak terpasang ganda.

| File | Kegunaan |
|---|---|
| `database/dapurnda-phpmyadmin-full.sql` | **Direkomendasikan untuk instalasi baru di localhost atau cPanel.** Berisi struktur terbaru, kategori, 24 resep contoh, dan akun Super Admin awal. |
| `database/schema.sql` | Struktur dasar terbaru dengan data awal minimum. Cocok jika ingin memulai dari skema dasar. |
| `database/sample-recipes.sql` | Menambahkan 18 resep ke 6 resep awal dari `schema.sql`, sehingga katalog demo berisi 24 resep. |
| `database/migrate-2026-11-13.sql` | Migrasi satu kali untuk database versi lama: data kontak/persetujuan kontributor dan indeks urutan resep. |
| `database/migrate-2026-11-13-admin-roles.sql` | Migrasi satu kali untuk database lama: peran Admin/Super Admin, status akun, dan pembaruan akun `rinda`. |

### Pilihan A — instalasi baru yang lengkap

Impor hanya:

```text
database/dapurnda-phpmyadmin-full.sql
```

### Pilihan B — struktur dasar dan data contoh terpisah

Impor secara berurutan:

```text
1. database/schema.sql
2. database/sample-recipes.sql   # opsional
```

### Pilihan C — memperbarui database lama

1. Buat backup database terlebih dahulu.
2. Pastikan migrasi tersebut memang belum pernah dijalankan.
3. Impor `migrate-2026-11-13.sql`.
4. Impor `migrate-2026-11-13-admin-roles.sql`.
5. Periksa kembali akun, status resep, dan halaman dashboard.

> File migrasi harus dijalankan **satu kali**. Menjalankannya kembali dapat menghasilkan kesalahan kolom atau indeks yang sudah ada.

## Konfigurasi aplikasi

Konfigurasi utama berada di `config/config.php`. Aplikasi memprioritaskan environment variables dan memakai nilai di file tersebut sebagai fallback.

| Environment variable | Contoh | Keterangan |
|---|---|---|
| `APP_NAME` | `DapurNda` | Nama aplikasi. |
| `APP_URL` | `https://dapurnda.example` | URL publik tanpa slash di akhir. Wajib diisi saat produksi. |
| `APP_ENV` | `production` | Lingkungan aplikasi: `local` atau `production`. |
| `APP_DEBUG` | `0` | Gunakan `0` di produksi agar detail error tidak tampil ke publik. |
| `APP_TIMEZONE` | `Asia/Jakarta` | Zona waktu aplikasi. |
| `APP_PRIVATE_ROOT` | `/home/akun/resep` | Folder privat yang berisi `src/`, jika namanya bukan `resep` atau lokasinya khusus. |
| `DB_HOST` | `localhost` | Host database dari cPanel/hosting. |
| `DB_PORT` | `3306` | Port MySQL. |
| `DB_NAME` | `akun_dapurnda` | Nama database, biasanya memakai prefix akun cPanel. |
| `DB_USER` | `akun_dapurnda` | User database yang sudah diberi hak akses. |
| `DB_PASS` | `password-kuat` | Password user database. |
| `APP_SETUP_KEY` | string acak panjang | Kunci untuk membuat admin pertama jika tabel admin masih kosong. |

Contoh nilai produksi tersedia di `config/config.example.php`. File tersebut adalah **referensi**, bukan file yang otomatis dibaca oleh aplikasi.

### Pengaturan penting produksi

Gunakan setidaknya nilai berikut:

```text
APP_URL=https://domain-anda.com
APP_ENV=production
APP_DEBUG=0
APP_TIMEZONE=Asia/Jakarta
```

Jika hosting tidak menyediakan menu environment variable, sesuaikan fallback pada `config/config.php`. Jangan memasukkan password database produksi ke repository publik.

## Akun pengelola awal

Installer lengkap `database/dapurnda-phpmyadmin-full.sql` membuat akun awal berikut:

```text
Username : rinda
Password : Satu23456
Peran    : Super Admin
```

### Wajib setelah login pertama

1. Masuk melalui `/admin/`.
2. Buka menu **Pengguna**.
3. Ganti password bawaan dengan password panjang dan unik.
4. Pastikan hanya akun yang diperlukan yang aktif.
5. Jangan publikasikan website sebelum langkah ini selesai.

Jika menggunakan skema tanpa akun admin, buka `/admin/`, lalu buat admin pertama dengan `APP_SETUP_KEY`. Akun pertama otomatis menjadi **Super Admin**.

Sistem mengunci login selama 15 menit setelah 5 percobaan password yang gagal pada akun yang sama.

## Deployment ke cPanel

### Struktur folder yang direkomendasikan

Kode privat ditempatkan di luar `public_html`; hanya isi folder `public/` yang boleh terbuka ke web.

```text
/home/CPANEL_USER/
├── resep/                         # Tidak dapat diakses langsung dari web
│   ├── config/
│   │   └── config.php
│   ├── database/                  # Boleh disimpan sementara/private
│   ├── src/
│   └── storage/
│       └── logs/
│
└── public_html/                   # Document root domain
    ├── admin/
    ├── api/
    ├── assets/
    ├── uploads/
    ├── index.php
    ├── recipe.php
    ├── search.php
    ├── add.php
    ├── app.php
    ├── .htaccess
    └── file publik lainnya
```

Artinya:

- Salin `config/`, `src/`, `storage/`, dan bila dibutuhkan `database/` ke `/home/CPANEL_USER/resep/`.
- Salin **isi** folder `public/`, bukan folder `public` itu sendiri, ke `/home/CPANEL_USER/public_html/`.
- Jangan memindahkan `config.php` atau file SQL ke dalam `public_html`.

`public/app.php` otomatis mencari kode inti di folder privat bernama `resep`. Jika menggunakan nama lain, atur `APP_PRIVATE_ROOT` ke path absolut folder tersebut.

### Langkah deployment

1. **Buat backup** file dan database lama jika ini pembaruan.
2. Di cPanel, buat database dan user MySQL.
3. Hubungkan user ke database dengan **All Privileges**.
4. Buka phpMyAdmin dan impor `database/dapurnda-phpmyadmin-full.sql` untuk instalasi baru.
5. Upload kode privat ke `/home/CPANEL_USER/resep/`.
6. Upload isi `public/` ke `/home/CPANEL_USER/public_html/`.
7. Masukkan kredensial database cPanel pada environment atau `config/config.php` privat.
8. Isi `APP_URL` dengan domain HTTPS yang sebenarnya.
9. Set `APP_ENV=production` dan `APP_DEBUG=0`.
10. Pastikan `public_html/uploads/` dan `resep/storage/logs/` dapat ditulis PHP.
11. Aktifkan SSL/HTTPS dan paksa redirect HTTPS dari pengaturan hosting bila tersedia.
12. Login ke area pengelola dan langsung ganti password bawaan.
13. Uji halaman resep, pencarian, upload gambar, statistik, dan tombol berbagi.

### Rekomendasi permission

| Lokasi | Permission awal |
|---|---|
| Folder umum | `755` |
| File PHP/CSS/JS | `644` |
| `public_html/uploads/` | `755`, atau `775` jika PHP belum dapat menulis |
| `resep/storage/logs/` | `755`, atau `775` jika PHP belum dapat menulis |
| `config/config.php` | `640` atau permission paling ketat yang masih dapat dibaca PHP |

Hindari permission `777`. Jika hosting menggunakan user PHP yang sama dengan pemilik akun, `755` biasanya sudah cukup.

### Checklist sebelum publikasi

- [ ] Domain sudah memakai HTTPS.
- [ ] `APP_URL` berisi domain publik HTTPS.
- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=0`.
- [ ] Kredensial database produksi sudah benar.
- [ ] `APP_SETUP_KEY` sudah diganti dengan nilai acak panjang.
- [ ] Password akun `rinda` sudah diganti.
- [ ] Folder `config/`, `src/`, `database/`, dan `storage/` berada di luar `public_html`.
- [ ] Upload gambar berhasil dan file PHP tidak dapat dijalankan dari folder uploads.
- [ ] Tampilan mobile diuji pada ponsel atau DevTools.
- [ ] Open Graph diuji menggunakan URL publik, bukan localhost.
- [ ] Backup database dan file tersedia.

## Alur kerja aplikasi

### Moderasi resep

```text
Pengunjung mengirim resep
          │
          ▼
Status: menunggu
          │
          ▼
Admin memeriksa isi dan kontak
          │
     ┌────┴────┐
     ▼         ▼
  Disetujui   Ditolak
     │
     ▼
Tampil di website publik
```

Admin dapat melihat identitas/kontak yang diberikan kontributor, status moderasi, dan waktu persetujuan. Data pribadi tersebut tidak ditampilkan sebagai informasi publik kecuali memang digunakan pada bagian resep yang telah dirancang untuk publikasi.

### Perhitungan tayangan

- Tayangan dicatat saat halaman detail resep dibuka.
- Satu sesi tidak dihitung berulang kali untuk resep yang sama.
- Bot, crawler, dan social preview tidak dimasukkan sebagai tayangan normal.
- Jumlah tayangan dipakai untuk menentukan resep populer di beranda dan peringkat pada dashboard.

### Sponsor

Fitur sponsor publik dinonaktifkan secara bawaan melalui:

```php
'features' => [
    'public_sponsors' => false,
],
```

Data dan halaman pengelolaan tetap tersedia untuk persiapan. Ubah menjadi `true` hanya setelah penempatan sponsor, materi visual, tautan, serta kebijakan publikasinya siap.

## Keamanan

Implementasi yang tersedia meliputi:

- Query database menggunakan PDO prepared statements.
- Password admin disimpan dengan `password_hash()` dan diverifikasi menggunakan `password_verify()`.
- Password lama otomatis di-rehash jika algoritma PHP berubah.
- Regenerasi session ID setelah login dan logout.
- Pemisahan peran Admin dan Super Admin.
- Penguncian akun sementara setelah percobaan login gagal berulang.
- Validasi CSRF pada aksi form yang mengubah data.
- Validasi username, password, status, dan input resep di server.
- Rate limit pengiriman resep.
- Pemeriksaan MIME asli gambar melalui Fileinfo.
- Nama file upload acak.
- Eksekusi script dinonaktifkan di folder `uploads/` melalui `.htaccess`.
- Proteksi akses file sensitif dan directory listing.
- Header keamanan seperti `X-Content-Type-Options` jika `mod_headers` tersedia.

### Catatan keamanan operasional

- Jangan menyimpan password produksi di repository publik.
- Walaupun repository bersifat privat, rotasi credential jika pernah terpapar pada log, screenshot, atau pihak lain.
- Backup database secara berkala.
- Perbarui PHP dan MySQL dari cPanel ke versi yang masih didukung.
- Jangan menampilkan `APP_DEBUG=1` pada website publik.
- Hapus file arsip `.zip` dari `public_html` setelah deployment selesai.

## SEO dan berbagi ke media sosial

Halaman resep menyiapkan:

- Judul dan deskripsi unik.
- Canonical URL.
- Gambar Open Graph.
- Open Graph untuk WhatsApp, Telegram, Facebook, dan platform kompatibel lain.
- Twitter Card.
- Recipe JSON-LD untuk mesin pencari.
- Sitemap dinamis.
- Robots endpoint.

Agar preview muncul dengan benar:

1. `APP_URL` harus berisi domain HTTPS publik.
2. Foto resep harus dapat dibuka tanpa login.
3. Server tidak boleh memblokir crawler sosial.
4. Gambar harus memiliki format yang didukung dan ukuran wajar.
5. Jika preview lama masih muncul, gunakan debugger platform terkait karena metadata biasanya tersimpan di cache.

Tombol berbagi menyediakan opsi WhatsApp, Telegram, Facebook, native Web Share API pada perangkat yang mendukung, dan salin tautan sebagai fallback.

## Pengelolaan gambar

Pengguna dapat memilih gambar asli hingga batas sumber **25 MB**. Sistem kemudian mencoba menyimpan hasil yang sudah dioptimalkan dengan target maksimal **500 KB**.

Format yang diterima:

- JPEG/JPG
- PNG
- WebP

Alur pemrosesan:

1. Browser mengoptimalkan foto sebelum pengiriman jika fitur tersedia.
2. Server memeriksa error upload, ukuran sumber, jumlah piksel, dan MIME asli.
3. Jika ekstensi GD tersedia, server mengubah ukuran/kualitas secara bertahap.
4. File disimpan dengan nama acak di `public/uploads/`.
5. Jika gambar tidak dapat diperkecil dengan aman, pengguna menerima pesan untuk memilih foto lain.

Konfigurasi terkait berada pada `config/config.php`:

```php
'upload_max_bytes' => 500 * 1024,
'upload_source_max_bytes' => 25 * 1024 * 1024,
'upload_max_pixels' => 20_000_000,
```

Nilai `upload_max_filesize` dan `post_max_size` dari PHP/hosting tetap harus cukup besar untuk menerima file sumber sebelum diproses.

## Rute penting

| URL | Fungsi |
|---|---|
| `/` | Beranda dan resep populer. |
| `/search.php` | Pencarian, kategori, grid/list, dan semua resep. |
| `/recipe.php?slug=...` | Detail serta tutorial resep. |
| `/add.php` | Form kontribusi resep. |
| `/about.php` | Tentang DapurNda. |
| `/privacy.php` | Kebijakan privasi. |
| `/terms.php` | Syarat penggunaan. |
| `/sitemap.xml` | Sitemap dinamis melalui rewrite Apache. |
| `/robots.txt` | Robots dinamis/fallback. |
| `/admin/` | Login dan dashboard pengelola. |
| `/admin/recipes.php` | Moderasi dan pengelolaan resep. |
| `/admin/sponsors.php` | Pengelolaan sponsor. |
| `/admin/inquiries.php` | Permintaan sponsor. |
| `/admin/users.php` | Pengelolaan akun, khusus Super Admin. |

## Panduan pengembangan

### Strategi branch

Setiap fitur, perbaikan bug, atau dokumentasi sebaiknya dikerjakan pada branch terpisah:

```text
feat/nama-fitur
fix/nama-bug
docs/nama-dokumentasi
chore/nama-pekerjaan
```

Contoh:

```bash
git switch main
git pull --ff-only
git switch -c feat/favorit-resep
```

Setelah perubahan selesai:

```bash
git status
git diff --check
git add <file-yang-diubah>
git commit -m "feat: tambahkan favorit resep"
git push -u origin feat/favorit-resep
```

### Pemeriksaan sebelum commit

- Jalankan pemeriksaan sintaks pada file PHP yang berubah:

  ```bash
  php -l public/recipe.php
  ```

- Pastikan tidak ada whitespace error:

  ```bash
  git diff --check
  ```

- Uji desktop dan mobile.
- Uji akun Admin serta Super Admin jika mengubah dashboard.
- Uji database baru dan, bila relevan, migrasi database lama.
- Periksa bahwa credential lokal/produksi tidak ikut masuk commit.

### Data SQL di repository

File SQL proyek memang dipertahankan agar instalasi phpMyAdmin mudah dilakukan. Repository privat mengurangi risiko akses publik, tetapi file SQL tetap tidak boleh berisi dump data pribadi, password database produksi, token, atau credential sensitif lain.

## Pemecahan masalah

### “Kode inti aplikasi tidak ditemukan”

`public/app.php` tidak menemukan `src/bootstrap.php`.

Periksa struktur folder privat. Jika folder tidak bernama `resep`, set:

```text
APP_PRIVATE_ROOT=/home/CPANEL_USER/nama-folder-privat
```

Path tersebut harus berisi `src/bootstrap.php`.

### Koneksi database gagal

Periksa:

- Nama database cPanel biasanya memiliki prefix, misalnya `akun_dapurnda`.
- User database juga biasanya memiliki prefix.
- User sudah ditambahkan ke database dengan hak akses yang benar.
- Host database sesuai informasi hosting; sering kali `localhost`.
- Password tidak memiliki karakter yang terpotong saat disalin.

Aktifkan debug hanya sementara pada lingkungan aman, lalu kembalikan ke `APP_DEBUG=0`.

### Muncul pengingat untuk mengisi `APP_URL`

Artinya aplikasi belum mengetahui alamat publik. Isi dengan domain lengkap:

```text
https://domain-anda.com
```

Jangan isi path localhost setelah website berada di hosting. `APP_URL` digunakan untuk canonical URL, gambar, sitemap, dan tautan berbagi.

### Gambar gagal diunggah atau dikompres

Periksa:

- Ekstensi PHP GD dan Fileinfo aktif.
- `upload_max_filesize` minimal 25 MB.
- `post_max_size` lebih besar dari `upload_max_filesize`.
- `memory_limit` cukup untuk memproses gambar besar.
- `public/uploads/` dapat ditulis oleh PHP.
- Gambar bukan file rusak dan menggunakan JPEG, PNG, atau WebP.

### Preview WhatsApp/Telegram tidak muncul

- URL `localhost` tidak dapat diakses oleh crawler platform.
- Pastikan `APP_URL` memakai HTTPS.
- Buka URL gambar Open Graph langsung dari jaringan publik.
- Pastikan sertifikat SSL valid.
- Tunggu atau bersihkan cache preview melalui debugger platform.

### Halaman 500 atau kosong

1. Periksa versi PHP dan ekstensi yang dibutuhkan.
2. Periksa `storage/logs/` atau error log di cPanel.
3. Periksa credential database.
4. Pastikan file privat dapat dibaca oleh PHP.
5. Aktifkan debug sementara hanya untuk diagnosis, lalu nonaktifkan kembali.

### CSS atau logo lama masih terlihat

Lakukan hard refresh, bersihkan cache browser/CDN, dan pastikan seluruh isi `public/assets/` versi terbaru sudah diunggah.

### Layout dapat digeser ke kanan dan kiri

Pastikan seluruh file CSS dan JavaScript terbaru terunggah, tidak ada cache aset lama, dan tidak ada konten HTML tambahan dari plugin/hosting. Uji pada mode incognito sebelum menyimpulkan masalah masih berasal dari kode terbaru.

## Status fitur

| Fitur | Status |
|---|---|
| Website resep responsif | Siap |
| Pencarian, kategori, grid/list | Siap |
| Detail dan tutorial memasak | Siap |
| Kontribusi resep bertahap | Siap |
| Moderasi resep | Siap |
| Dashboard statistik dan tayangan | Siap |
| Admin dan Super Admin | Siap |
| Kompresi gambar otomatis | Siap, memerlukan GD |
| Open Graph dan social sharing | Siap, memerlukan domain HTTPS |
| Pengelolaan sponsor | Siap di admin |
| Sponsor di website publik | Dinonaktifkan sementara |

## Kredit dan lisensi

- Area pengelola menggunakan **Sneat Free Bootstrap HTML Admin Template** oleh [ThemeSelection](https://themeselection.com/item/sneat-free-bootstrap-html-admin-template/).
- Ketentuan aset Sneat yang disertakan dapat dibaca pada `public/assets/admin/LICENSE-SNEAT.md`.
- Logo, identitas visual, konten resep, dan kode khusus aplikasi merupakan bagian dari proyek DapurNda.

Repository ini belum menyertakan lisensi umum proyek pada root. Sampai pemilik proyek menetapkan lisensi, jangan menganggap kode DapurNda bebas digunakan, dijual kembali, atau didistribusikan. Lisensi pihak ketiga tetap mengikuti ketentuan masing-masing.

---

<div align="center">

**DapurNda** — dibuat untuk membantu lebih banyak orang merasa nyaman memasak dari dapurnya sendiri.

</div>

# Rancangan Aplikasi Resep Masak (PHP + MySQL)

Dokumen ini menjabarkan konsep sebelum pembuatan kode: login, upload gambar, database, halaman, tampilan, dan struktur folder.

---

## 1. Apakah perlu login?

**Melihat resep: tidak perlu login.** Semua orang bisa membuka, mencari, dan memfilter resep.

**Menambah resep:** ada tiga pilihan.

| Pilihan | Cara kerja | Kelebihan | Kekurangan |
|---|---|---|---|
| A. Tanpa login, langsung tayang | Isi form, resep langsung muncul | Paling mudah dipakai | Rawan spam dan konten sampah |
| B. Wajib login | Daftar dulu, baru bisa tambah resep | Aman, ada pemilik resep | Pengguna malas daftar, kodenya lebih banyak |
| C. Tanpa login + moderasi (**disarankan**) | Isi form, resep berstatus *pending*, tayang setelah disetujui admin | Mudah dipakai dan tetap aman | Admin harus menyetujui satu per satu |

Dengan pilihan C, hanya **admin** yang punya login, yaitu untuk menyetujui, mengedit, atau menghapus resep. Pengguna umum tidak perlu akun.

Untuk mengurangi spam:

- Honeypot (kolom tersembunyi untuk menjebak bot)
- Batas jumlah submit per IP, misalnya maksimal 5 resep per jam
- Opsional: kode PIN saat submit, supaya penulis bisa mengedit resepnya sendiri nanti tanpa akun

---

## 2. Upload gambar (maksimal 1 foto, otomatis dioptimalkan hingga 500 KB)

Batasan dicek di dua tempat:

- **Di browser:** pengguna cukup memilih foto asli dari HP. Foto otomatis dikecilkan dan dikompres ke WebP/JPEG hingga maksimal 500 KB, lalu ditampilkan ukuran awal dan hasil optimasinya.
- **Di server (PHP):** tipe dan dimensi gambar diperiksa lagi, nama file diganti acak, dan foto di atas 500 KB dikompres ulang bila ekstensi GD tersedia. Folder upload juga dikunci supaya file PHP tidak bisa dijalankan dari sana.

Database hanya menyimpan nama file, sedangkan fotonya disimpan di folder `uploads/`.

---

## 3. Struktur database (MySQL)

| Tabel | Kolom |
|---|---|
| `categories` | id, nama, slug (contoh: Sarapan, Makan Siang, Dessert) |
| `recipes` | id, judul, slug, deskripsi, id kategori, waktu masak, porsi, tingkat kesulitan, nama file foto, status (`pending` / `approved`), nama penulis (opsional), PIN edit (di-hash), tanggal dibuat |
| `recipe_ingredients` | id resep, nama bahan, takaran |
| `recipe_steps` | id resep, urutan, isi langkah |
| `admins` | id, username, password (di-hash) |
| `sponsors` | id, nama, logo, banner, deskripsi singkat, link tujuan, tier (Utama / Emas / Perak), posisi tayang, tanggal mulai, tanggal berakhir, status aktif, jumlah tayang, jumlah klik |
| `sponsor_inquiries` | id, nama, email/WhatsApp, nama brand, pesan, tanggal (dari form "Jadi Sponsor") |

Tabel `recipes` mendapat tambahan kolom `sponsor_id` (boleh kosong) untuk resep yang disponsori.

Bahan dan langkah dibuat sebagai tabel terpisah supaya form bisa menambah baris secara dinamis, misalnya tombol "+ Tambah bahan".

---

## 4. Halaman yang akan dibuat

**Publik (tanpa login):**

- **Beranda:** hero dan lima resep yang paling sering dibuka, dilengkapi tombol menuju semua resep
- **Detail resep:** foto, bahan (bisa dicentang), langkah-langkah, info waktu dan porsi
- **Tambah resep:** form dengan preview foto dan penghitung ukuran file
- **Jadi sponsor (fase berikutnya):** halaman profil paket sponsor dan form kontak; sementara disembunyikan dari website publik

**Admin:**

- Login
- Daftar resep *pending*
- Tombol setujui / tolak / hapus / edit
- Kelola sponsor: tambah, edit, aktif/nonaktif, atur posisi dan masa tayang, lihat statistik tayang dan klik
- Lihat pesan masuk dari calon sponsor

---

## 5. Tampilan dan animasi

- Desain modern dengan warna hangat bertema makanan, responsif untuk HP dan desktop
- Kartu resep muncul bertahap (fade-in saat di-scroll), dengan efek hover (naik dan foto zoom halus)
- Pencarian langsung (live search) tanpa reload halaman
- Skeleton loading saat data dimuat
- Transisi halus antar halaman
- Toast notifikasi saat berhasil menambah resep
- Animasi pada checklist bahan
- Mode gelap (opsional)

---

## 6. Struktur folder

```
resep/
├── index.php          (beranda)
├── recipe.php         (detail)
├── add.php            (tambah resep)
├── sponsor.php        (halaman jadi sponsor + form kontak)
├── go.php             (redirect ke link sponsor + hitung klik)
├── admin/             (login, dashboard, moderasi, kelola sponsor)
├── api/               (search, submit, sponsor)
├── includes/          (koneksi DB, fungsi, validasi upload)
├── assets/            (css, js)
├── uploads/           (foto resep + sponsors/, dikunci dari eksekusi PHP)
└── database.sql
```

Semua ini berjalan di hosting PHP + MySQL biasa, tanpa framework.

---

## 7. Fitur sponsor

Sponsor dikelola penuh oleh **admin**. Pengunjung umum tetap tidak perlu login.

> Status saat ini: fitur dan dashboard sponsor tetap tersedia di kode, tetapi seluruh penempatan serta halaman sponsor publik dinonaktifkan melalui `features.public_sponsors` sampai program komersialnya siap diluncurkan.

**Tempat sponsor tampil:**

| Posisi | Tampilan |
|---|---|
| Beranda, bagian atas | Banner sponsor utama berupa carousel dengan transisi halus (tier Utama) |
| Beranda, di bawah hero | Strip logo "Didukung oleh" yang bergerak pelan (marquee), logo berwarna saat di-hover |
| Di tengah grid resep | Kartu **Resep Disponsori** dengan label "Disponsori", tampilannya serasi dengan kartu resep biasa |
| Detail resep | Banner sponsor di samping (desktop) atau di bawah bahan (HP) |
| Footer | Daftar logo semua sponsor aktif |

**Cara kerjanya:**

- Admin mengunggah logo dan banner, mengisi link tujuan, memilih tier dan posisi, lalu menentukan tanggal mulai dan berakhir. Sponsor otomatis tayang dan berhenti sesuai tanggal.
- Setiap klik melewati `go.php` sehingga jumlah klik tercatat. Jumlah tayang juga dihitung. Admin bisa melihat statistik per sponsor sebagai bahan laporan ke sponsor.
- Link keluar diberi atribut `rel="sponsored noopener"` agar sesuai standar SEO.
- Halaman **Jadi Sponsor** berisi penjelasan paket dan form kontak. Pesan masuk tersimpan dan bisa dibaca di dashboard admin.
- Gambar sponsor memakai target yang sama dengan foto resep: otomatis dioptimalkan hingga maksimal 500 KB, dengan tipe file dan dimensi dicek di server.

**Tampilan dan animasi sponsor:** carousel dengan fade dan slide, label "Disponsori" yang jelas tetapi tidak mengganggu, efek hover halus pada logo, dan skeleton loading supaya halaman tidak melompat saat banner dimuat.

---

## 8. Keputusan final

- **Penambahan resep:** pilihan **C**. Tanpa login, resep berstatus *pending* dan tayang setelah disetujui admin.
- **Optimasi gambar:** pengguna dapat memilih foto asli tanpa mengompres manual; sistem menargetkan hasil akhir maksimal **500 KB**. Resep memiliki maksimal 1 foto; sponsor dapat memiliki 1 logo dan 1 banner.
- **Login:** hanya admin. Pengunjung umum tidak perlu akun.

---

## 9. Status pembuatan

Kode sudah dibuat langsung di workspace proyek dengan folder `public/` sebagai web root lokal.

**Sudah ada:**

- Beranda dengan pencarian langsung, filter kategori, dan animasi kartu
- Detail resep dengan bahan yang bisa dicentang
- Form tambah resep: wizard empat langkah di mobile, 1 foto, kompres otomatis di browser, cek ulang di server, honeypot, batas 5 resep per jam per IP
- Admin: buat akun pertama, setujui atau hapus resep, kelola sponsor
- Infrastruktur sponsor: banner, logo di footer, label "Disponsori", hitung tayang dan klik, serta masa tayang otomatis (publik sementara dinonaktifkan)
- Halaman "Jadi Sponsor" dan form kontak sudah tersedia, tetapi sementara dialihkan kembali ke beranda
- Status resep `pending`, `approved`, dan `rejected`
- Edit resep dari dashboard admin dan tautkan resep ke sponsor tertentu
- Meta Open Graph, Twitter Card, Recipe JSON-LD, sitemap dinamis, serta tombol berbagi WhatsApp, Telegram, Facebook, native share, dan salin tautan
- Halaman admin untuk membaca dan memperbarui status pesan calon sponsor
- Desain responsif dengan custom clip, reveal animation, motion kartu, indikator scroll, marquee pesan, toast, dan preferensi reduced motion
- Pencarian modern, daftar bahan dengan progres, serta mode memasak interaktif yang menyimpan langkah selesai di perangkat
- Foto makanan realistis untuk semua resep contoh dan hero beranda, dioptimalkan sebagai WebP sekitar 95–150 KB
- Navigasi cepat mobile, target sentuh lebih besar, label kesulitan, serta panduan khusus pengguna yang baru belajar masak

**Belum ada:**

- PIN edit resep oleh penulis
- Mode gelap
- CAPTCHA eksternal jika spam meningkat
- Pengujian preview sosial pada domain HTTPS final

---

## 10. Cara menjalankan dan memasang

**Localhost dengan Apache + phpMyAdmin:**

1. Pastikan Apache dan MySQL aktif.
2. Buka `http://localhost/phpmyadmin/`, buat database `resep`, lalu import `database/schema.sql` (database lokal pada workspace ini sudah dibuat dan diimpor saat implementasi).
3. Sesuaikan koneksi lokal di `config/config.php` jika username atau password MySQL berbeda.
4. Buka `http://localhost/resep/`; halaman otomatis mengarah ke `http://localhost/resep/public/`.
5. Buka `http://localhost/resep/public/admin/` untuk membuat akun admin pertama.

**Hosting cPanel:**

1. Buat database MySQL di cPanel, lalu import `database/schema.sql` lewat phpMyAdmin.
2. Isi kredensial database, `APP_URL`, dan `APP_SETUP_KEY` di environment hosting atau `config/config.php`.
3. Letakkan `config/`, `src/`, `database/`, dan `storage/` di `/home/user/resep/`.
4. Pindahkan seluruh isi folder `public/` ke `/home/user/public_html/`. Loader `app.php` otomatis mencari folder privat `/home/user/resep/`.
5. Pastikan folder `public_html/uploads/` bisa ditulis (permission 755, atau 775 jika upload gagal).
6. Buka `/admin/`. Saat belum ada akun, halaman itu menampilkan form untuk membuat admin pertama.
7. Masuk sebagai admin, tambahkan sponsor, lalu coba ajukan resep dari halaman publik dan setujui dari dashboard.

---

## 11. Pengamanan untuk go public

**Sudah diterapkan di kode:**

- Query database memakai prepared statement, semua output di-escape, dan form dilindungi token CSRF
- Header keamanan (CSP, anti-iframe, nosniff), cookie sesi `HttpOnly` dan `SameSite`, serta `Secure` otomatis di HTTPS
- Error tidak ditampilkan ke pengunjung, hanya dicatat di log server
- Kredensial database dan seluruh kode inti dipisah di luar web root; file `.sql`, `.md`, log, dan konfigurasi tidak ikut terbuka ke web
- Upload: foto asli dikompres otomatis hingga maksimal 500 KB, tipe dicek dari isi file, dimensi dibatasi, nama diacak, dan eksekusi PHP di `uploads/` diblokir
- Form tambah resep: honeypot, batas 5 resep per jam per IP, batas panjang teks, batas 40 bahan dan 40 langkah
- Admin: pembuatan akun pertama butuh `SETUP_KEY`, password minimal 6 karakter, login dikunci 15 menit setelah 5 kali gagal, halaman admin tidak diindeks mesin pencari
- Index database untuk pencarian dan filter

**Wajib dilakukan sebelum go public:**

1. Ganti `APP_SETUP_KEY`, isi `APP_URL`, dan isi kredensial database di `config/config.php` atau environment hosting.
2. Aktifkan HTTPS (SSL gratis di cPanel) dan paksa HTTP ke HTTPS.
3. Hapus `database.sql` dari hosting setelah diimport.
4. Buat akun admin pertama, lalu ganti `SETUP_KEY` dengan nilai acak baru.
5. Aktifkan backup berkala di cPanel (database dan folder `uploads/`).
6. Pastikan PHP versi 8.1 atau lebih baru.

**Disarankan menyusul:** paginasi beranda (sekarang 60 resep terbaru), CAPTCHA (Cloudflare Turnstile) jika spam meningkat, dan Cloudflare di depan situs untuk perlindungan DDoS.

---

## 12. Struktur proyek yang direkomendasikan

Implementasi memakai struktur berikut agar pengembangan lokal dan deployment cPanel tetap konsisten.

```
/home/user/resep/            (di luar public_html, tidak bisa diakses web)
├── config/
│   └── config.php           (kredensial database dan SETUP_KEY)
├── src/
│   ├── db.php               (koneksi dan fungsi query)
│   ├── helpers.php          (escape, CSRF, validasi teks)
│   ├── bootstrap.php        (inisialisasi aplikasi dan keamanan)
│   ├── auth.php             (login dan pengamanan admin)
│   ├── upload.php           (validasi dan simpan gambar)
│   ├── recipes.php          (query dan penyimpanan resep)
│   ├── sponsors.php         (ambil sponsor, hitung tayang)
│   └── layout.php           (header dan footer halaman)
├── database/schema.sql
└── storage/logs/

/home/user/public_html/      (hanya ini yang terbuka ke publik)
├── index.php                (beranda)
├── search.php               (halaman khusus pencarian resep)
├── recipe.php               (detail resep)
├── add.php                  (tambah resep)
├── sponsor.php              (paket sponsor dan form kontak)
├── go.php                   (redirect dan hitung klik sponsor)
├── sitemap.php              (sitemap XML dinamis)
├── api/search.php           (live search)
├── admin/
│   ├── index.php            (login dan dashboard)
│   ├── recipes.php          (moderasi resep)
│   ├── recipe-edit.php      (edit dan tautkan sponsor)
│   ├── sponsors.php         (kelola sponsor)
│   ├── inquiries.php        (pesan calon sponsor)
│   └── logout.php
├── assets/                  (style.css, app.js)
├── uploads/                 (foto resep dan sponsor, eksekusi PHP diblokir)
├── .htaccess
└── robots.txt
```

**Kelebihan dibanding struktur ringkas:**

- `config.php`, kode inti, dan file SQL berada di luar folder web, jadi tidak bisa dibuka lewat browser sama sekali. Keamanan tidak lagi bergantung pada `.htaccess`.
- Tugas tiap file jelas (database, upload, sponsor, layout), sehingga lebih mudah dirawat dan diuji.
- Halaman admin dipecah per fitur, jadi lebih mudah ditambah, misalnya edit resep atau pesan dari calon sponsor.
- File di `public_html` hanya berisi halaman tampilan yang tipis.

**Catatan pemasangan:** `public/app.php` menjadi loader tunggal. Pada localhost loader menemukan `../src/bootstrap.php`; pada susunan cPanel loader menemukan `../resep/src/bootstrap.php`. Jika folder privat memakai lokasi lain, atur environment variable `APP_PRIVATE_ROOT`.

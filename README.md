# DapurNda

Aplikasi berbagi resep berbasis PHP 8.1+ dan MySQL. Pengunjung dapat mencari, membaca, membagikan, dan mengajukan resep tanpa akun. Admin memoderasi resep, mengelola sponsor, serta membaca permintaan kolaborasi.

Tampilan publik dioptimalkan untuk penggunaan ponsel dengan target sentuh besar, navigasi bawah, enam resep terpopuler di beranda, pencarian langsung dengan pilihan Grid/List dan tombol Muat lainnya, formulir tulis resep bertahap, daftar bahan interaktif, mode memasak dengan progres tersimpan, serta foto yang otomatis dioptimalkan hingga maksimal 500 KB. Aset foto resep berada di `public/assets/images/recipes/`.

Fitur sponsor publik sementara dinonaktifkan melalui `features.public_sponsors` di `config/config.php`. Halaman dan pengelolaan sponsor tetap disimpan di area admin agar dapat diaktifkan nanti tanpa membangun ulang sistem.

## Menjalankan di localhost

1. Buat database bernama `resep` melalui phpMyAdmin.
2. Impor `database/schema.sql` ke database tersebut.
3. Impor `database/sample-recipes.sql` untuk menambahkan pustaka 24 resep contoh lengkap.
4. Sesuaikan koneksi di `config/config.php` bila username atau password MySQL berbeda.
5. Dengan Apache localhost yang sudah aktif, buka:

   ```text
   http://localhost/resep/
   ```

   Halaman root akan mengarahkan ke `http://localhost/resep/public/` agar kode privat tetap terpisah.

6. Buka `http://localhost/resep/public/admin/` untuk membuat admin pertama. Setup key lokal awal ada di `config/config.php`; ganti sebelum publikasi.

Untuk instalasi lama, impor `database/migrate-2026-11-13.sql` satu kali, kemudian impor `database/sample-recipes.sql`.

Jika ingin URL lokal tanpa `/public`, buat Apache Alias `/resep` yang langsung mengarah ke folder `resep/public/`. Ini hanya kosmetik dan tidak diperlukan agar aplikasi berfungsi.

## Pengaturan produksi

- Pindahkan isi `public/` ke `public_html/`.
- Letakkan `config/`, `src/`, `database/`, dan `storage/` di luar `public_html`.
- Loader `public/app.php` otomatis mencari kode inti di folder saudara `resep/`. Jika lokasi privat berbeda, isi `APP_PRIVATE_ROOT` dengan path folder privat tersebut.
- Isi `APP_URL` dengan URL HTTPS publik agar canonical URL dan preview WhatsApp/Telegram memakai alamat yang benar.
- Set `APP_ENV=production`, `APP_DEBUG=0`, kredensial database, dan `APP_SETUP_KEY` acak melalui environment variables atau konfigurasi hosting.
- Sitemap dan robots dibuat dinamis melalui `public/sitemap.php` serta `public/robots.php`; aturan Apache yang disediakan mengarahkan `/sitemap.xml` dan `/robots.txt` secara otomatis.
- Pastikan `public/uploads/` dapat ditulis oleh PHP dan HTTPS aktif.
- Aktifkan ekstensi PHP GD sebagai cadangan kompresi di server. Alur normal tetap mengoptimalkan foto di browser sebelum upload.
- Pada konfigurasi Apache produksi, gunakan `ServerTokens Prod`, `ServerSignature Off`, dan nonaktifkan `expose_php` agar versi server tidak dikirim melalui header.

Preview sosial tidak dapat diuji dari `localhost`; WhatsApp, Telegram, dan Facebook harus dapat mengakses URL HTTPS publik beserta gambar Open Graph-nya.

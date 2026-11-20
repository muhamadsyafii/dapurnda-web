-- ============================================================
-- DapurNda - Instalasi lengkap untuk phpMyAdmin / cPanel
-- Dibuat: 13 November 2026
-- Versi schema: 2026.11.13-r2
-- Mencakup: moderasi, tayangan resep, role Admin/Super Admin,
-- status akun aktif/nonaktif, dan pengelolaan pengguna.
--
-- Cara pakai:
-- 1. Buat database dan user MySQL melalui cPanel.
-- 2. Buka database tersebut di phpMyAdmin.
-- 3. Pilih menu Import lalu unggah berkas ini.
--
-- Akun pengelola awal:
-- Username : rinda
-- Password : Satu23456
-- Role     : Super Admin
--
-- Tidak ada CREATE DATABASE atau USE agar kompatibel dengan
-- penamaan database cPanel (biasanya username_namadatabase).
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS categories (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(90) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sponsors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    logo_filename VARCHAR(100) NULL,
    banner_filename VARCHAR(100) NULL,
    description VARCHAR(500) NOT NULL DEFAULT '',
    destination_url VARCHAR(1000) NOT NULL,
    tier ENUM('utama', 'emas', 'perak') NOT NULL DEFAULT 'perak',
    positions VARCHAR(255) NOT NULL DEFAULT 'footer',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    impressions BIGINT UNSIGNED NOT NULL DEFAULT 0,
    clicks BIGINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sponsors_active_dates (is_active, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recipes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id SMALLINT UNSIGNED NOT NULL,
    sponsor_id INT UNSIGNED NULL,
    title VARCHAR(160) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    cook_time SMALLINT UNSIGNED NOT NULL,
    servings TINYINT UNSIGNED NOT NULL,
    difficulty ENUM('Mudah', 'Sedang', 'Menantang') NOT NULL DEFAULT 'Mudah',
    photo_filename VARCHAR(100) NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    author_name VARCHAR(100) NULL,
    contributor_contact VARCHAR(160) NULL,
    publishing_consent_at TIMESTAMP NULL,
    edit_pin_hash VARCHAR(255) NULL,
    views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_recipes_category FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_recipes_sponsor FOREIGN KEY (sponsor_id) REFERENCES sponsors(id) ON DELETE SET NULL,
    INDEX idx_recipes_status_created (status, created_at),
    INDEX idx_recipes_category_status (category_id, status),
    INDEX idx_recipes_sponsor (sponsor_id),
    FULLTEXT INDEX ft_recipes_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recipe_ingredients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    amount VARCHAR(100) NOT NULL DEFAULT '',
    sort_order SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT fk_ingredients_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE INDEX uq_ingredients_recipe_order (recipe_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recipe_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    instruction TEXT NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT fk_steps_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE INDEX uq_steps_recipe_order (recipe_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(40) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admins_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sponsor_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(160) NOT NULL,
    brand_name VARCHAR(140) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'contacted', 'closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inquiries_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_submission_ip_created (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categories (id, name, slug) VALUES
    (1, 'Sarapan', 'sarapan'),
    (2, 'Makan Siang', 'makan-siang'),
    (3, 'Makan Malam', 'makan-malam'),
    (4, 'Camilan', 'camilan'),
    (5, 'Dessert', 'dessert'),
    (6, 'Minuman', 'minuman'),
    (7, 'Nusantara', 'nusantara'),
    (8, 'Sehat', 'sehat');

INSERT IGNORE INTO recipes
    (id, category_id, title, slug, description, cook_time, servings, difficulty, photo_filename, status, author_name, published_at)
VALUES
    (1, 7, 'Nasi Goreng Kampung Harum', 'nasi-goreng-kampung-harum', 'Nasi goreng rumahan dengan aroma terasi, cabai, dan kecap yang akrab. Cepat dibuat, kaya rasa, dan cocok untuk menu kapan saja.', 25, 2, 'Mudah', 'asset:nasi-goreng-kampung.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (2, 1, 'Bubur Ayam Jahe Hangat', 'bubur-ayam-jahe-hangat', 'Bubur lembut dengan kaldu ayam dan sentuhan jahe. Sarapan menenangkan yang tetap penuh rasa.', 50, 4, 'Sedang', 'asset:bubur-ayam-jahe.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (3, 8, 'Gado-Gado Saus Kacang', 'gado-gado-saus-kacang', 'Sayuran rebus segar, tahu, tempe, dan saus kacang gurih-manis yang dibuat dari bahan sederhana.', 35, 4, 'Mudah', 'asset:gado-gado.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (4, 5, 'Klepon Pandan Lumer', 'klepon-pandan-lumer', 'Klepon kenyal beraroma pandan dengan isian gula aren yang meleleh dan balutan kelapa gurih.', 45, 6, 'Sedang', 'asset:klepon-pandan.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (5, 3, 'Ayam Bakar Madu Rempah', 'ayam-bakar-madu-rempah', 'Ayam berbumbu rempah dengan lapisan madu berkaramel. Juicy di dalam dan harum di luar.', 70, 4, 'Sedang', 'asset:ayam-bakar-madu.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (6, 6, 'Es Cendol Gula Aren', 'es-cendol-gula-aren', 'Cendol pandan, santan ringan, dan sirup gula aren asli untuk minuman penutup yang menyegarkan.', 30, 4, 'Mudah', 'asset:es-cendol.webp', 'approved', 'DapurNda', UTC_TIMESTAMP());

INSERT IGNORE INTO recipe_ingredients (id, recipe_id, name, amount, sort_order) VALUES
    (1, 1, 'Nasi putih dingin', '2 piring', 1), (2, 1, 'Telur', '2 butir', 2), (3, 1, 'Bawang merah', '5 siung', 3), (4, 1, 'Bawang putih', '3 siung', 4), (5, 1, 'Cabai merah', '3 buah', 5), (6, 1, 'Terasi bakar', '1/2 sdt', 6), (7, 1, 'Kecap manis', '2 sdm', 7),
    (8, 2, 'Beras', '200 g', 1), (9, 2, 'Dada ayam', '250 g', 2), (10, 2, 'Jahe memarkan', '3 cm', 3), (11, 2, 'Kaldu ayam', '1,5 liter', 4), (12, 2, 'Daun bawang', '2 batang', 5),
    (13, 3, 'Kentang', '2 buah', 1), (14, 3, 'Tauge', '150 g', 2), (15, 3, 'Tahu dan tempe', 'masing-masing 150 g', 3), (16, 3, 'Kacang tanah goreng', '200 g', 4), (17, 3, 'Gula merah', '60 g', 5),
    (18, 4, 'Tepung ketan', '250 g', 1), (19, 4, 'Air pandan', '200 ml', 2), (20, 4, 'Gula aren', '120 g', 3), (21, 4, 'Kelapa parut', '200 g', 4),
    (22, 5, 'Ayam', '1 ekor, potong 8', 1), (23, 5, 'Madu', '3 sdm', 2), (24, 5, 'Kecap manis', '4 sdm', 3), (25, 5, 'Ketumbar bubuk', '1 sdt', 4), (26, 5, 'Bawang putih', '5 siung', 5),
    (27, 6, 'Cendol', '300 g', 1), (28, 6, 'Santan', '500 ml', 2), (29, 6, 'Gula aren', '200 g', 3), (30, 6, 'Daun pandan', '2 lembar', 4), (31, 6, 'Es batu', 'secukupnya', 5);

INSERT IGNORE INTO recipe_steps (id, recipe_id, instruction, sort_order) VALUES
    (1, 1, 'Haluskan bawang, cabai, dan terasi. Tumis sampai benar-benar harum.', 1), (2, 1, 'Sisihkan bumbu, masukkan telur, lalu orak-arik sampai matang.', 2), (3, 1, 'Masukkan nasi dan kecap. Aduk dengan api besar hingga bumbu rata dan nasi sedikit berasap.', 3),
    (4, 2, 'Rebus ayam bersama jahe dan sedikit garam hingga matang. Angkat ayam lalu suwir.', 1), (5, 2, 'Masukkan beras ke dalam kaldu. Masak dengan api kecil sambil sesekali diaduk hingga menjadi bubur.', 2), (6, 2, 'Sajikan bubur dengan ayam suwir, daun bawang, bawang goreng, dan kecap sesuai selera.', 3),
    (7, 3, 'Rebus sayuran satu per satu agar tingkat kematangannya pas. Goreng tahu dan tempe.', 1), (8, 3, 'Haluskan kacang, gula merah, cabai, dan garam. Encerkan perlahan dengan air hangat.', 2), (9, 3, 'Susun sayuran, tahu, dan tempe. Siram saus kacang sesaat sebelum disajikan.', 3),
    (10, 4, 'Campur tepung ketan dengan air pandan sedikit demi sedikit hingga adonan dapat dibentuk.', 1), (11, 4, 'Ambil sedikit adonan, isi gula aren, rapatkan, lalu bulatkan.', 2), (12, 4, 'Rebus sampai klepon mengapung. Angkat dan langsung gulingkan pada kelapa parut kukus.', 3),
    (13, 5, 'Haluskan bawang dan rempah. Campur dengan kecap, madu, dan sedikit air.', 1), (14, 5, 'Ungkep ayam bersama bumbu sampai matang dan bumbu meresap.', 2), (15, 5, 'Bakar ayam sambil dioles sisa bumbu hingga permukaannya berkaramel.', 3),
    (16, 6, 'Masak gula aren dengan sedikit air dan daun pandan, lalu saring dan dinginkan.', 1), (17, 6, 'Hangatkan santan dengan sedikit garam tanpa membuatnya pecah. Dinginkan.', 2), (18, 6, 'Susun cendol, es, santan, dan sirup gula aren di dalam gelas.', 3);

-- Data lengkap 18 resep tambahan (total menjadi 24 resep).
INSERT IGNORE INTO recipes
    (category_id, title, slug, description, cook_time, servings, difficulty, photo_filename, status, author_name, published_at)
VALUES
    (7, 'Soto Ayam Kuning Rumahan', 'soto-ayam-kuning-rumahan', 'Soto ayam berkuah kuning bening dengan suwiran ayam, soun, kol, dan taburan bawang goreng yang hangat serta segar.', 55, 4, 'Sedang', 'asset:soto-ayam.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (7, 'Rendang Sapi Bumbu Pekat', 'rendang-sapi-bumbu-pekat', 'Rendang sapi dengan santan dan rempah yang dimasak perlahan sampai bumbunya gelap, pekat, dan meresap ke dalam daging.', 180, 6, 'Menantang', 'asset:rendang-sapi.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (2, 'Sayur Asem Segar', 'sayur-asem-segar', 'Kuah asam segar berisi jagung, labu siam, kacang panjang, dan kacang tanah untuk pendamping makan siang keluarga.', 40, 5, 'Mudah', 'asset:sayur-asem.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (3, 'Opor Ayam Gurih Lembut', 'opor-ayam-gurih-lembut', 'Ayam empuk dalam kuah santan berbumbu ringan dengan aroma serai dan daun jeruk, cocok untuk makan bersama keluarga.', 65, 5, 'Sedang', 'asset:opor-ayam.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (3, 'Mie Goreng Jawa', 'mie-goreng-jawa', 'Mie goreng manis gurih dengan ayam, telur, kol, dan aroma bawang yang dimasak cepat menggunakan api besar.', 30, 3, 'Mudah', 'asset:mie-goreng-jawa.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (8, 'Capcay Kuah Warna-Warni', 'capcay-kuah-warna-warni', 'Sayuran warna-warni dan ayam dalam kuah gurih ringan, praktis untuk menambah menu sayur harian keluarga.', 35, 4, 'Mudah', 'asset:capcay-kuah.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (8, 'Tumis Kangkung Terasi', 'tumis-kangkung-terasi', 'Kangkung tetap hijau dan renyah dengan bumbu terasi, bawang, cabai, dan tomat yang harum menggugah selera.', 18, 3, 'Mudah', 'asset:tumis-kangkung.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (2, 'Pepes Ikan Kemangi', 'pepes-ikan-kemangi', 'Ikan berbumbu kunyit, kemangi, tomat, dan cabai yang dikukus dalam daun pisang hingga harum dan lembut.', 55, 4, 'Sedang', 'asset:pepes-ikan.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (4, 'Perkedel Kentang Gurih', 'perkedel-kentang-gurih', 'Perkedel kentang berkulit keemasan dengan bagian dalam lembut, gurih, dan tidak mudah hancur ketika digoreng.', 40, 5, 'Mudah', 'asset:perkedel-kentang.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (4, 'Bakwan Jagung Renyah', 'bakwan-jagung-renyah', 'Bakwan jagung dengan butiran jagung manis, daun bawang, dan tepian renyah untuk camilan atau lauk sederhana.', 25, 4, 'Mudah', 'asset:bakwan-jagung.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (4, 'Pisang Goreng Krispi', 'pisang-goreng-krispi', 'Pisang matang berbalut adonan tipis yang renyah di luar dan tetap lembut manis di dalam, cocok ditemani teh.', 25, 4, 'Mudah', 'asset:pisang-goreng.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (5, 'Kolak Pisang Gula Aren', 'kolak-pisang-gula-aren', 'Kolak pisang dan ubi dengan kuah santan gula aren yang lembut, harum pandan, dan tidak terasa terlalu manis.', 35, 6, 'Mudah', 'asset:kolak-pisang.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (5, 'Puding Santan Gula Merah', 'puding-santan-gula-merah', 'Puding dua lapis dengan rasa gula merah dan santan yang lembut, cantik disajikan sebagai pencuci mulut keluarga.', 45, 8, 'Mudah', 'asset:puding-gula-merah.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (6, 'Wedang Jahe Hangat', 'wedang-jahe-hangat', 'Minuman jahe, serai, kayu manis, dan gula aren yang menghangatkan dengan aroma rempah yang menenangkan.', 20, 3, 'Mudah', 'asset:wedang-jahe.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (6, 'Es Buah Segar', 'es-buah-segar', 'Campuran melon, pepaya, nanas, nata de coco, dan biji selasih dalam kuah ringan yang dingin dan menyegarkan.', 20, 5, 'Mudah', 'asset:es-buah.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (3, 'Ayam Kecap Bawang', 'ayam-kecap-bawang', 'Ayam empuk dengan kecap manis, bawang bombai, tomat, dan cabai yang membentuk kuah pekat gurih-manis.', 45, 4, 'Mudah', 'asset:ayam-kecap.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (2, 'Sup Ayam Sayur Bening', 'sup-ayam-sayur-bening', 'Sup ayam berkuah bening dengan wortel, kentang, kol, dan seledri yang ringan sekaligus menghangatkan.', 50, 5, 'Mudah', 'asset:sup-ayam.webp', 'approved', 'DapurNda', UTC_TIMESTAMP()),
    (1, 'Nasi Uduk Betawi Komplet', 'nasi-uduk-betawi-komplet', 'Nasi gurih santan dengan ayam goreng, telur, tempe orek, mentimun, dan sambal untuk sarapan yang mengenyangkan.', 75, 4, 'Sedang', 'asset:nasi-uduk.webp', 'approved', 'DapurNda', UTC_TIMESTAMP());

INSERT IGNORE INTO recipe_ingredients (recipe_id, name, amount, sort_order) VALUES
    ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Dada ayam', '500 g', 1), ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Soun', '100 g', 2), ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Kol iris', '150 g', 3), ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Kunyit dan bawang putih', 'secukupnya', 4), ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Kaldu ayam', '1,5 liter', 5),
    ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Daging sapi', '1 kg', 1), ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Santan kental', '1,2 liter', 2), ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Cabai merah giling', '120 g', 3), ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Serai dan daun jeruk', 'secukupnya', 4), ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Bumbu bawang dan rempah', '200 g', 5),
    ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Jagung manis', '2 buah', 1), ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Labu siam', '1 buah', 2), ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Kacang panjang', '8 batang', 3), ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Asam jawa', '2 sdm', 4), ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Kacang tanah', '80 g', 5),
    ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Ayam', '1 ekor, potong 8', 1), ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Santan', '1 liter', 2), ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Bawang merah dan putih', 'secukupnya', 3), ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Kemiri', '5 butir', 4), ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Serai dan daun jeruk', 'secukupnya', 5),
    ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Mie telur', '300 g', 1), ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Daging ayam', '150 g', 2), ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Telur', '2 butir', 3), ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Kol dan daun bawang', '200 g', 4), ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Kecap manis', '3 sdm', 5),
    ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Wortel', '1 buah', 1), ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Kembang kol', '200 g', 2), ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Pakcoy', '150 g', 3), ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Daging ayam', '150 g', 4), ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Kaldu', '500 ml', 5),
    ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Kangkung', '2 ikat', 1), ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Bawang putih', '3 siung', 2), ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Cabai merah', '3 buah', 3), ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Terasi bakar', '1/2 sdt', 4), ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Tomat', '1 buah', 5),
    ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Ikan kembung', '4 ekor', 1), ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Daun kemangi', '2 genggam', 2), ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Tomat', '2 buah', 3), ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Bumbu kuning', '150 g', 4), ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Daun pisang', 'secukupnya', 5),
    ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Kentang', '700 g', 1), ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Daging cincang', '100 g', 2), ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Daun seledri', '2 batang', 3), ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Telur', '2 butir', 4), ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Bawang goreng', '2 sdm', 5),
    ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Jagung manis', '3 buah', 1), ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Tepung terigu', '120 g', 2), ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Daun bawang', '2 batang', 3), ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Bawang putih', '3 siung', 4), ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Air', '120 ml', 5),
    ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Pisang kepok', '8 buah', 1), ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Tepung terigu', '150 g', 2), ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Tepung beras', '50 g', 3), ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Gula', '2 sdm', 4), ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Air', '180 ml', 5),
    ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Pisang kepok', '8 buah', 1), ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Ubi oranye', '300 g', 2), ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Gula aren', '200 g', 3), ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Santan', '600 ml', 4), ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Daun pandan', '2 lembar', 5),
    ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Agar-agar bubuk', '2 bungkus', 1), ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Gula merah', '180 g', 2), ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Santan', '700 ml', 3), ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Air', '500 ml', 4), ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Daun pandan', '2 lembar', 5),
    ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Jahe', '100 g', 1), ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Serai', '2 batang', 2), ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Kayu manis', '5 cm', 3), ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Gula aren', '100 g', 4), ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Air', '800 ml', 5),
    ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Melon', '200 g', 1), ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Pepaya', '200 g', 2), ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Nanas', '200 g', 3), ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Nata de coco', '150 g', 4), ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Biji selasih dan sirup', 'secukupnya', 5),
    ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Ayam', '800 g', 1), ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Kecap manis', '5 sdm', 2), ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Bawang bombai', '1 buah', 3), ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Tomat', '2 buah', 4), ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Bawang putih', '4 siung', 5),
    ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Ayam', '500 g', 1), ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Wortel', '2 buah', 2), ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Kentang', '2 buah', 3), ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Kol', '200 g', 4), ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Seledri dan daun bawang', 'secukupnya', 5),
    ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Beras', '500 g', 1), ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Santan', '650 ml', 2), ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Serai dan daun salam', 'secukupnya', 3), ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Ayam goreng dan telur', '4 porsi', 4), ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Tempe orek dan sambal', 'secukupnya', 5);

INSERT IGNORE INTO recipe_steps (recipe_id, instruction, sort_order) VALUES
    ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Rebus ayam hingga matang, angkat dan suwir. Sisihkan kaldunya.', 1), ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Tumis bumbu kuning hingga harum lalu masukkan ke dalam kaldu dan didihkan.', 2), ((SELECT id FROM recipes WHERE slug='soto-ayam-kuning-rumahan'), 'Susun soun, kol, dan ayam lalu siram kuah panas dan beri pelengkap.', 3),
    ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Masak santan, cabai, bumbu halus, serai, dan daun jeruk sambil diaduk.', 1), ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Masukkan daging dan masak dengan api kecil hingga santan menyusut.', 2), ((SELECT id FROM recipes WHERE slug='rendang-sapi-bumbu-pekat'), 'Terus aduk perlahan sampai bumbu gelap, berminyak, dan melekat pada daging.', 3),
    ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Didihkan air bersama bumbu halus, asam jawa, dan kacang tanah.', 1), ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Masukkan jagung dan labu siam, masak sampai mulai empuk.', 2), ((SELECT id FROM recipes WHERE slug='sayur-asem-segar'), 'Tambahkan kacang panjang, koreksi rasa, lalu masak sebentar agar tetap renyah.', 3),
    ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Tumis bumbu halus, serai, dan daun jeruk sampai matang dan harum.', 1), ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Masukkan ayam dan aduk sampai permukaannya berubah warna.', 2), ((SELECT id FROM recipes WHERE slug='opor-ayam-gurih-lembut'), 'Tuang santan lalu masak dengan api kecil hingga ayam empuk tanpa membuat santan pecah.', 3),
    ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Rebus mie sebentar, tiriskan, lalu campur dengan sedikit kecap.', 1), ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Tumis bumbu, ayam, dan telur sampai matang lalu masukkan sayuran.', 2), ((SELECT id FROM recipes WHERE slug='mie-goreng-jawa'), 'Masukkan mie dan sisa kecap, aduk cepat dengan api besar sampai rata.', 3),
    ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Tumis bawang dan ayam hingga harum serta berubah warna.', 1), ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Masukkan wortel, kembang kol, dan kaldu lalu masak hingga setengah empuk.', 2), ((SELECT id FROM recipes WHERE slug='capcay-kuah-warna-warni'), 'Tambahkan pakcoy, bumbui, dan masak singkat agar sayuran tetap segar.', 3),
    ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Petik kangkung, cuci bersih, dan tiriskan sampai tidak terlalu basah.', 1), ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Tumis bawang, cabai, dan terasi dengan api besar hingga harum.', 2), ((SELECT id FROM recipes WHERE slug='tumis-kangkung-terasi'), 'Masukkan kangkung dan tomat, aduk cepat dua sampai tiga menit lalu angkat.', 3),
    ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Lumuri ikan dengan bumbu kuning lalu diamkan selama lima belas menit.', 1), ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Bungkus ikan bersama kemangi dan tomat menggunakan daun pisang.', 2), ((SELECT id FROM recipes WHERE slug='pepes-ikan-kemangi'), 'Kukus hingga matang lalu bakar sebentar agar daun dan bumbunya lebih harum.', 3),
    ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Goreng kentang sampai empuk lalu haluskan selagi hangat.', 1), ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Campur kentang dengan daging, seledri, dan bawang goreng lalu bentuk bulat pipih.', 2), ((SELECT id FROM recipes WHERE slug='perkedel-kentang-gurih'), 'Celupkan ke telur kocok dan goreng sampai kedua sisinya keemasan.', 3),
    ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Serut sebagian jagung dan tumbuk ringan agar adonan menyatu.', 1), ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Campur jagung dengan tepung, bumbu, daun bawang, dan air sedikit demi sedikit.', 2), ((SELECT id FROM recipes WHERE slug='bakwan-jagung-renyah'), 'Goreng sesendok demi sesendok hingga tipis, matang, dan renyah.', 3),
    ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Belah pisang sesuai selera tanpa memutuskannya seluruhnya.', 1), ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Campur dua jenis tepung, gula, dan air hingga menjadi adonan agak encer.', 2), ((SELECT id FROM recipes WHERE slug='pisang-goreng-krispi'), 'Celupkan pisang lalu goreng dalam minyak panas sampai keemasan.', 3),
    ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Rebus ubi bersama air dan daun pandan sampai hampir empuk.', 1), ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Masukkan gula aren yang telah disaring dan potongan pisang.', 2), ((SELECT id FROM recipes WHERE slug='kolak-pisang-gula-aren'), 'Tuang santan dan masak dengan api kecil sambil diaduk agar tidak pecah.', 3),
    ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Masak lapisan gula merah dengan satu bungkus agar-agar lalu tuang ke cetakan.', 1), ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Setelah permukaan mulai set, masak lapisan santan dan agar-agar sisanya.', 2), ((SELECT id FROM recipes WHERE slug='puding-santan-gula-merah'), 'Tuang perlahan di atas lapisan pertama, dinginkan, lalu potong setelah padat.', 3),
    ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Bakar atau memarkan jahe agar aromanya lebih kuat.', 1), ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Rebus air bersama jahe, serai, dan kayu manis selama lima belas menit.', 2), ((SELECT id FROM recipes WHERE slug='wedang-jahe-hangat'), 'Masukkan gula aren, aduk hingga larut, lalu saring dan sajikan hangat.', 3),
    ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Potong semua buah dengan ukuran sekali makan dan siapkan es batu.', 1), ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Rendam biji selasih sampai mengembang lalu tiriskan.', 2), ((SELECT id FROM recipes WHERE slug='es-buah-segar'), 'Campur buah, nata de coco, selasih, sirup, air, dan es sesaat sebelum disajikan.', 3),
    ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Tumis bawang putih dan bawang bombai sampai harum dan layu.', 1), ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Masukkan ayam lalu aduk sampai permukaannya berubah warna.', 2), ((SELECT id FROM recipes WHERE slug='ayam-kecap-bawang'), 'Tambahkan kecap, tomat, dan sedikit air lalu masak hingga ayam empuk dan kuah pekat.', 3),
    ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Rebus ayam sampai keluar kaldunya, buang buih agar kuah tetap bening.', 1), ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Masukkan kentang dan wortel lalu masak sampai hampir empuk.', 2), ((SELECT id FROM recipes WHERE slug='sup-ayam-sayur-bening'), 'Tambahkan kol dan daun aromatik, bumbui, lalu masak sebentar sebelum disajikan.', 3),
    ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Cuci beras lalu masak bersama santan, serai, daun salam, dan sedikit garam.', 1), ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Aduk sekali saat santan mulai terserap lalu lanjutkan sampai nasi matang.', 2), ((SELECT id FROM recipes WHERE slug='nasi-uduk-betawi-komplet'), 'Sajikan nasi uduk dengan ayam, telur, tempe orek, mentimun, dan sambal.', 3);

-- Akun pengelola awal. INSERT IGNORE menjaga akun lama jika berkas
-- ini tidak sengaja diimpor ulang pada database yang sudah terisi.
INSERT IGNORE INTO admins (username, password_hash, role, is_active)
VALUES ('rinda', '$2y$10$8WsaM1IbvZcBhwA.VQxKJ.n7GO2s0W5pfMw51YAdO9BWvAbDgqram', 'super_admin', 1);

SET FOREIGN_KEY_CHECKS = 1;

-- Ringkasan hasil yang seharusnya terlihat setelah impor:
-- 8 kategori, 24 resep, 1 akun pengelola.
SELECT 'Instalasi DapurNda selesai' AS status,
       (SELECT COUNT(*) FROM categories) AS categories,
       (SELECT COUNT(*) FROM recipes) AS recipes,
       (SELECT COUNT(*) FROM admins) AS admins,
       (SELECT COUNT(*) FROM admins WHERE role = 'super_admin' AND is_active = 1) AS active_super_admins;


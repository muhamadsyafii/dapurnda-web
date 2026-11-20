SET NAMES utf8mb4;
SET time_zone = '+00:00';

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

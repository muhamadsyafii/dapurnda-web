-- Jalankan satu kali pada database DapurNda lama melalui phpMyAdmin.
-- Setelah migrasi, akun rinda menjadi Super Admin dan dapat mengelola pengguna.
ALTER TABLE admins
    ADD COLUMN role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin' AFTER password_hash,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role,
    ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD INDEX idx_admins_role_active (role, is_active);

UPDATE admins SET role = 'super_admin', is_active = 1 WHERE username = 'rinda';

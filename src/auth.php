<?php

declare(strict_types=1);

function admin_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
}

function create_first_admin(string $username, string $password, string $setupKey): void
{
    if (admin_count() > 0) {
        throw new RuntimeException('Akun admin pertama sudah dibuat.');
    }
    if (!hash_equals((string) config('security.setup_key'), $setupKey)) {
        throw new InvalidArgumentException('Kunci setup tidak sesuai.');
    }
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) {
        throw new InvalidArgumentException('Username harus 3–40 karakter dan hanya memakai huruf, angka, titik, garis bawah, atau tanda hubung.');
    }
    if (mb_strlen($password) < 6) {
        throw new InvalidArgumentException('Password minimal 6 karakter.');
    }

    $statement = db()->prepare("INSERT INTO admins (username, password_hash, role, is_active) VALUES (?, ?, 'super_admin', 1)");
    $statement->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
}

function attempt_admin_login(string $username, string $password): bool
{
    $admin = db_one('SELECT * FROM admins WHERE username = ? LIMIT 1', [$username]);
    if (!$admin) {
        usleep(250000);
        return false;
    }

    if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
        throw new RuntimeException('Login sementara dikunci. Silakan coba lagi setelah 15 menit.');
    }

    if (!password_verify($password, $admin['password_hash'])) {
        $attempts = (int) $admin['failed_attempts'] + 1;
        $lockedUntil = $attempts >= 5 ? gmdate('Y-m-d H:i:s', time() + 900) : null;
        $statement = db()->prepare('UPDATE admins SET failed_attempts = ?, locked_until = ? WHERE id = ?');
        $statement->execute([$attempts >= 5 ? 0 : $attempts, $lockedUntil, $admin['id']]);
        return false;
    }

    if (!(bool) ($admin['is_active'] ?? true)) {
        throw new RuntimeException('Akun ini sedang dinonaktifkan. Hubungi Super Admin.');
    }

    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    }

    db()->prepare('UPDATE admins SET failed_attempts = 0, locked_until = NULL, last_login_at = UTC_TIMESTAMP() WHERE id = ?')
        ->execute([$admin['id']]);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
    return true;
}

function current_admin(): ?array
{
    static $loadedAdminId = null;
    static $loadedAdmin = null;

    $sessionAdminId = $_SESSION['admin_id'] ?? null;
    if (!is_int($sessionAdminId)) {
        return null;
    }
    if ($loadedAdminId === $sessionAdminId) {
        return $loadedAdmin;
    }

    $loadedAdminId = $sessionAdminId;
    $loadedAdmin = db_one('SELECT id, username, role, is_active, last_login_at, created_at FROM admins WHERE id = ? LIMIT 1', [$sessionAdminId]);
    if (!$loadedAdmin || !(bool) $loadedAdmin['is_active']) {
        unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
        $loadedAdmin = null;
        return null;
    }

    $_SESSION['admin_username'] = $loadedAdmin['username'];
    $_SESSION['admin_role'] = $loadedAdmin['role'];
    return $loadedAdmin;
}

function is_admin(): bool
{
    return current_admin() !== null;
}

function is_super_admin(): bool
{
    return (current_admin()['role'] ?? '') === 'super_admin';
}

function admin_role_label(?string $role = null): string
{
    $role ??= (string) (current_admin()['role'] ?? 'admin');
    return $role === 'super_admin' ? 'Super Admin' : 'Admin';
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('error', 'Silakan masuk sebagai admin.');
        redirect('admin/index.php');
    }
}

function require_super_admin(): void
{
    require_admin();
    if (!is_super_admin()) {
        flash('error', 'Halaman pengguna hanya dapat diakses oleh Super Admin.');
        redirect('admin/index.php');
    }
}

function validate_admin_account(string $username, string $password, string $passwordConfirmation, string $role, bool $passwordRequired): array
{
    $username = trim($username);
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) {
        throw new InvalidArgumentException('Username harus 3–40 karakter dan hanya memakai huruf, angka, titik, garis bawah, atau tanda hubung.');
    }
    if (!in_array($role, ['super_admin', 'admin'], true)) {
        throw new InvalidArgumentException('Pilih peran pengguna yang tersedia.');
    }
    if ($passwordRequired && $password === '') {
        throw new InvalidArgumentException('Password wajib diisi untuk pengguna baru.');
    }
    if ($password !== '' && mb_strlen($password) < 6) {
        throw new InvalidArgumentException('Password minimal 6 karakter.');
    }
    if ($password !== $passwordConfirmation) {
        throw new InvalidArgumentException('Konfirmasi password tidak sama.');
    }
    return ['username' => $username, 'password' => $password, 'role' => $role];
}

function logout_admin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
    session_regenerate_id(true);
}

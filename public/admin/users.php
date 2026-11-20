<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';
require_super_admin();

$signedInAdmin = current_admin();
$signedInAdminId = (int) ($signedInAdmin['id'] ?? 0);

if (is_post()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'create');
    $targetId = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'delete') {
            if ($targetId === $signedInAdminId) {
                throw new RuntimeException('Akun yang sedang digunakan tidak dapat dihapus.');
            }
            $target = db_one('SELECT id, username, role, is_active FROM admins WHERE id = ?', [$targetId]);
            if (!$target) {
                throw new RuntimeException('Pengguna tidak ditemukan.');
            }
            if ($target['role'] === 'super_admin' && (bool) $target['is_active']) {
                $activeSuperAdmins = (int) db()->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin' AND is_active = 1")->fetchColumn();
                if ($activeSuperAdmins <= 1) {
                    throw new RuntimeException('Super Admin aktif terakhir tidak dapat dihapus.');
                }
            }
            db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$targetId]);
            flash('success', 'Pengguna ' . $target['username'] . ' berhasil dihapus.');
            redirect('admin/users.php', 303);
        }

        $passwordRequired = $action === 'create';
        $data = validate_admin_account(
            (string) ($_POST['username'] ?? ''),
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['password_confirmation'] ?? ''),
            (string) ($_POST['role'] ?? 'admin'),
            $passwordRequired
        );

        if ($action === 'create') {
            if (db_one('SELECT id FROM admins WHERE username = ? LIMIT 1', [$data['username']])) {
                throw new InvalidArgumentException('Username tersebut sudah digunakan.');
            }
            db()->prepare('INSERT INTO admins (username, password_hash, role, is_active) VALUES (?, ?, ?, 1)')
                ->execute([$data['username'], password_hash($data['password'], PASSWORD_DEFAULT), $data['role']]);
            flash('success', 'Pengguna baru berhasil dibuat dan sudah dapat login.');
            redirect('admin/users.php', 303);
        }

        if ($action !== 'update' || $targetId < 1) {
            throw new InvalidArgumentException('Tindakan pengguna tidak valid.');
        }
        $target = db_one('SELECT * FROM admins WHERE id = ?', [$targetId]);
        if (!$target) {
            throw new RuntimeException('Pengguna tidak ditemukan.');
        }
        if (db_one('SELECT id FROM admins WHERE username = ? AND id <> ? LIMIT 1', [$data['username'], $targetId])) {
            throw new InvalidArgumentException('Username tersebut sudah digunakan.');
        }

        $isSelf = $targetId === $signedInAdminId;
        $newRole = $isSelf ? 'super_admin' : $data['role'];
        $isActive = $isSelf ? 1 : (!empty($_POST['is_active']) ? 1 : 0);
        if (!$isSelf && $target['role'] === 'super_admin' && (bool) $target['is_active'] && ($newRole !== 'super_admin' || !$isActive)) {
            $activeSuperAdmins = (int) db()->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin' AND is_active = 1")->fetchColumn();
            if ($activeSuperAdmins <= 1) {
                throw new RuntimeException('Super Admin aktif terakhir tidak dapat diturunkan perannya atau dinonaktifkan.');
            }
        }

        $params = [$data['username'], $newRole, $isActive];
        $sql = 'UPDATE admins SET username = ?, role = ?, is_active = ?';
        if ($data['password'] !== '') {
            $sql .= ', password_hash = ?, failed_attempts = 0, locked_until = NULL';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id = ?';
        $params[] = $targetId;
        db()->prepare($sql)->execute($params);
        flash('success', 'Data pengguna berhasil diperbarui.');
        redirect('admin/users.php', 303);
    } catch (Throwable $exception) {
        flash('error', $exception->getMessage());
        $redirectPath = $action === 'update' && $targetId > 0 ? 'admin/users.php?edit=' . $targetId : 'admin/users.php';
        redirect($redirectPath, 303);
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = $editId ? db_one('SELECT * FROM admins WHERE id = ?', [(int) $editId]) : null;
$users = db_all("SELECT id, username, role, is_active, failed_attempts, locked_until, last_login_at, created_at FROM admins ORDER BY FIELD(role, 'super_admin', 'admin'), is_active DESC, username");
$activeCount = count(array_filter($users, static fn (array $user): bool => (bool) $user['is_active']));

function render_user_editor_modal(?array $user, int $signedInAdminId, string $modalId, bool $autoOpen = false): void
{
    $isEditing = $user !== null;
    $editingSelf = $isEditing && (int) $user['id'] === $signedInAdminId;
    ?>
    <div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-labelledby="<?= e($modalId) ?>-title" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-user-editor-modal<?= $autoOpen ? ' data-auto-open="true"' : '' ?>>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable admin-user-modal-dialog">
            <div class="modal-content admin-user-modal">
                <div class="modal-header">
                    <div><span class="panel-kicker"><?= $isEditing ? 'Perbarui akses' : 'Akun baru' ?></span><h2 class="modal-title" id="<?= e($modalId) ?>-title"><?= $isEditing ? 'Edit pengguna' : 'Tambah pengguna' ?></h2><p><?= $isEditing ? 'Kosongkan password jika tidak ingin menggantinya.' : 'Pengguna dapat login segera setelah akun dibuat.' ?></p></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form method="post" class="admin-user-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($user['id'] ?? 0) ?>">
                    <div class="modal-body">
                        <label class="field"><span>Username</span><input name="username" required minlength="3" maxlength="40" autocomplete="off" value="<?= e($user['username'] ?? '') ?>" placeholder="Contoh: pengelola_dapur"><small>Gunakan huruf, angka, titik, garis bawah, atau tanda hubung.</small></label>
                        <label class="field"><span>Peran</span><select name="role"<?= $editingSelf ? ' disabled' : '' ?>><option value="admin"<?= selected($user['role'] ?? 'admin', 'admin') ?>>Admin</option><option value="super_admin"<?= selected($user['role'] ?? 'admin', 'super_admin') ?>>Super Admin</option></select><?php if ($editingSelf): ?><input type="hidden" name="role" value="super_admin"><small>Peran akun yang sedang digunakan tidak dapat diturunkan.</small><?php else: ?><small>Super Admin dapat membuka menu Pengguna.</small><?php endif; ?></label>
                        <div class="field-grid">
                            <label class="field"><span><?= $isEditing ? 'Password baru' : 'Password' ?></span><input type="password" name="password"<?= $isEditing ? '' : ' required' ?> minlength="6" autocomplete="new-password"><small>Minimal 6 karakter.</small></label>
                            <label class="field"><span>Konfirmasi password</span><input type="password" name="password_confirmation"<?= $isEditing ? '' : ' required' ?> minlength="6" autocomplete="new-password"></label>
                        </div>
                        <?php if ($isEditing): ?>
                            <?php if ($editingSelf): ?><input type="hidden" name="is_active" value="1"><div class="self-protection-note"><?= icon('lock') ?><span>Akun yang sedang Anda gunakan akan tetap aktif.</span></div><?php else: ?><label class="user-active-toggle"><input type="checkbox" name="is_active" value="1"<?= checked((bool) $user['is_active']) ?>><span><i><?= icon('check') ?></i><b>Akun aktif</b><small>Pengguna dapat masuk ke dashboard.</small></span></label><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer"><button class="button button-outline" type="button" data-bs-dismiss="modal">Batal</button><button class="button button-primary" type="submit" name="action" value="<?= $isEditing ? 'update' : 'create' ?>"><?= $isEditing ? 'Simpan perubahan' : 'Buat pengguna' ?> <?= icon('arrow') ?></button></div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

render_admin_page_start('Kelola Pengguna', 'users');
?>
<main id="main-content" class="admin-shell container-xxl flex-grow-1 container-p-y">
    <div class="admin-top">
        <div><span class="admin-kicker">Akses dan keamanan</span><h1>Kelola pengguna</h1><p>Buat akun pengelola, tentukan tingkat akses, dan nonaktifkan akun yang sudah tidak digunakan.</p></div>
        <div class="admin-top-actions"><span class="admin-summary-chip"><?= icon('users') ?> <?= $activeCount ?> pengguna aktif</span><button class="button button-primary" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal"><?= icon('plus') ?> Tambah pengguna</button></div>
    </div>

    <aside class="admin-readiness user-role-guide"><strong>Perbedaan akses</strong><p><b>Admin</b> dapat mengelola resep, sponsor, dan pesan. <b>Super Admin</b> memiliki akses yang sama serta dapat menambah dan mengatur pengguna.</p></aside>

    <section class="admin-panel users-list-panel">
        <div class="panel-heading"><div><span class="panel-kicker">Tim pengelola</span><h2>Daftar pengguna</h2><p><?= count($users) ?> akun terdaftar di DapurNda.</p></div></div>
        <div class="admin-user-list">
            <?php foreach ($users as $user): ?>
                <?php $isSelf = (int) $user['id'] === $signedInAdminId; ?>
                <article class="admin-user-card<?= !(bool) $user['is_active'] ? ' is-inactive' : '' ?>">
                    <span class="admin-user-avatar"><?= e(mb_strtoupper(mb_substr($user['username'], 0, 1))) ?></span>
                    <div class="admin-user-identity">
                        <div><h3><?= e($user['username']) ?></h3><?php if ($isSelf): ?><span class="current-user-badge">Akun Anda</span><?php endif; ?></div>
                        <p><span class="role-badge role-<?= e($user['role']) ?>"><?= e(admin_role_label($user['role'])) ?></span><span class="account-state <?= $user['is_active'] ? 'is-active' : 'is-disabled' ?>"><?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></p>
                        <small><?= $user['last_login_at'] ? 'Login terakhir ' . e(format_date($user['last_login_at'], true)) : 'Belum pernah login' ?></small>
                    </div>
                    <div class="admin-user-actions">
                        <a href="<?= e(url('admin/users.php', ['edit' => $user['id']])) ?>"><?= icon('edit') ?> Edit</a>
                        <?php if (!$isSelf): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button class="danger" type="submit" name="action" value="delete" data-confirm="Hapus akun <?= e($user['username']) ?> secara permanen?"><?= icon('trash') ?><span>Hapus</span></button></form><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php render_user_editor_modal(null, $signedInAdminId, 'createUserModal'); ?>
<?php if ($editing): ?><?php render_user_editor_modal($editing, $signedInAdminId, 'editUserModal', true); ?><?php endif; ?>
<?php render_admin_page_end(); ?>

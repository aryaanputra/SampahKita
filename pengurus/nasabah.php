<?php
/**
 * pengurus/nasabah.php - Kelola data nasabah
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus']);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '') { $errors[] = 'Nama wajib diisi.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Format email tidak valid.'; }
        if (strlen($password) < 6) { $errors[] = 'Password minimal 6 karakter.'; }
        if ($nik !== '' && !preg_match('/^[0-9]{8,20}$/', $nik)) { $errors[] = 'NIK harus 8-20 angka.'; }

        if (empty($errors)) {
            $stmt = mysqli_prepare($con, "SELECT id_user FROM users WHERE email = ? OR nik = ? OR no_hp = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'sss', $email, $nik, $no_hp);
            mysqli_stmt_execute($stmt);
            $dup = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if ($dup) { $errors[] = 'Email/NIK/No. HP sudah terpakai.'; }
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($con, "INSERT INTO users (name, email, password, role, nik, no_hp, alamat, code, status, aktif) VALUES (?, ?, ?, 'nasabah', ?, ?, ?, 0, 'verified', 1)");
            mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $hash, $nik, $no_hp, $alamat);
            if (mysqli_stmt_execute($stmt)) {
                set_flash('success', 'Nasabah "' . $name . '" berhasil ditambahkan.');
                header('Location: nasabah.php');
                exit();
            }
            $errors[] = 'Gagal menyimpan data nasabah.';
        }
    }

    if ($aksi === 'ubah') {
        $id = (int) ($_POST['id_user'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');

        if ($name === '') { $errors[] = 'Nama wajib diisi.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Format email tidak valid.'; }

        if (empty($errors)) {
            $stmt = mysqli_prepare($con, "SELECT id_user FROM users WHERE (email = ? OR nik = ? OR no_hp = ?) AND id_user <> ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'sssi', $email, $nik, $no_hp, $id);
            mysqli_stmt_execute($stmt);
            $dup = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if ($dup) { $errors[] = 'Email/NIK/No. HP sudah dipakai nasabah lain.'; }
        }

        if (empty($errors)) {
            $stmt = mysqli_prepare($con, "UPDATE users SET name = ?, email = ?, nik = ?, no_hp = ?, alamat = ? WHERE id_user = ? AND role = 'nasabah'");
            mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $nik, $no_hp, $alamat, $id);
            if (mysqli_stmt_execute($stmt)) {
                set_flash('success', 'Data nasabah berhasil diperbarui.');
                header('Location: nasabah.php');
                exit();
            }
            $errors[] = 'Gagal memperbarui data.';
        }
    }

    if ($aksi === 'toggle') {
        $id = (int) ($_POST['id_user'] ?? 0);
        $stmt = mysqli_prepare($con, "UPDATE users SET aktif = 1 - aktif WHERE id_user = ? AND role = 'nasabah'");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        set_flash('info', 'Status akun nasabah diperbarui.');
        header('Location: nasabah.php');
        exit();
    }
}

$rows = [];
$res = mysqli_query($con, "SELECT * FROM users WHERE role = 'nasabah' ORDER BY name ASC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; } }

$edit = null;
if (isset($_GET['edit'])) {
    $edit = get_user($con, (int) $_GET['edit']);
    if ($edit && $edit['role'] !== 'nasabah') { $edit = null; }
}

$page_title    = 'Kelola Nasabah';
$page_subtitle = 'Daftar dan data diri seluruh nasabah terdaftar.';
$active_menu   = 'nasabah';
$page_actions  = '<button class="btn btn-dark-custom" type="button" data-scroll="#form-nasabah"><i class="bi bi-person-plus me-1"></i> ' . ($edit ? 'Form Edit' : 'Tambah Nasabah') . '</button>';
require __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert-custom alert-custom-danger mb-4">
    <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
    <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-5" id="form-nasabah">
    <div class="card">
      <div class="card-header"><h2 class="card-title"><?= $edit ? 'Edit Nasabah' : 'Tambah Nasabah' ?></h2></div>
      <form method="POST" action="nasabah.php">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
        <?php if ($edit): ?><input type="hidden" name="id_user" value="<?= e($edit['id_user']) ?>"><?php endif; ?>

        <div class="mb-3">
          <label class="form-label-custom">Nama Lengkap</label>
          <input type="text" class="form-control-custom" name="name" required value="<?= e($edit['name'] ?? ($_POST['name'] ?? '')) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label-custom">Email</label>
          <input type="email" class="form-control-custom" name="email" required value="<?= e($edit['email'] ?? ($_POST['email'] ?? '')) ?>">
        </div>
        <div class="row g-3 mb-3">
          <div class="col-sm-6"><label class="form-label-custom">NIK</label>
            <input type="text" class="form-control-custom" name="nik" pattern="[0-9]{8,20}" value="<?= e($edit['nik'] ?? ($_POST['nik'] ?? '')) ?>"></div>
          <div class="col-sm-6"><label class="form-label-custom">No. HP</label>
            <input type="text" class="form-control-custom" name="no_hp" pattern="[0-9+]{8,20}" value="<?= e($edit['no_hp'] ?? ($_POST['no_hp'] ?? '')) ?>"></div>
        </div>
        <div class="mb-3">
          <label class="form-label-custom">Alamat</label>
          <textarea class="form-control-custom" name="alamat" rows="2"><?= e($edit['alamat'] ?? ($_POST['alamat'] ?? '')) ?></textarea>
        </div>

        <?php if (!$edit): ?>
          <div class="mb-3">
            <label class="form-label-custom">Password Awal</label>
            <input type="text" class="form-control-custom" name="password" value="nasabah123" required>
            <small class="text-muted-green">Nasabah dapat mengganti password melalui fitur lupa password.</small>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-dark-custom flex-grow-1"><i class="bi bi-check-lg me-1"></i> Simpan</button>
          <?php if ($edit): ?><a href="nasabah.php" class="btn btn-dark-custom" style="background:var(--bs-body-bg);color:var(--text-main);">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="table-card-custom">
      <div class="table-header-control">
        <div class="table-search-box"><i class="bi bi-search table-search-icon"></i>
          <input type="text" class="table-search-input table-local-search" placeholder="Cari nasabah..."></div>
        <span class="badge-table info"><?= count($rows) ?> nasabah</span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Nasabah</th><th>NIK / HP</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="4" class="text-center text-muted-green py-4">Belum ada nasabah.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td>
                  <div class="table-user-cell">
                    <span class="table-user-avatar app-avatar-initial"><?= e(mb_substr($r['name'], 0, 1)) ?></span>
                    <div>
                      <div class="table-user-name"><?= e($r['name']) ?></div>
                      <div class="table-user-sub"><?= e($r['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td><?= e($r['nik'] ?: '-') ?><div class="table-user-sub"><?= e($r['no_hp'] ?: '-') ?></div></td>
                <td><?= badge_user($r) ?></td>
                <td class="text-center">
                  <div class="table-actions justify-content-center">
                    <a href="nasabah.php?edit=<?= e($r['id_user']) ?>" class="table-btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="nasabah.php" class="d-inline" data-confirm="Ubah status akun <?= e($r['name']) ?>?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="aksi" value="toggle">
                      <input type="hidden" name="id_user" value="<?= e($r['id_user']) ?>">
                      <button type="submit" class="table-btn-action" title="<?= ((int)$r['aktif'] === 1) ? 'Nonaktifkan' : 'Aktifkan' ?>">
                        <i class="bi <?= ((int)$r['aktif'] === 1) ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

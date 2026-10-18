<?php
/**
 * nasabah/jemput.php - Ajukan permintaan penjemputan sampah
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['nasabah']);

$u   = current_user();
$uid = $u['id_user'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $alamat  = trim($_POST['alamat'] ?? '');
    $jadwal  = trim($_POST['jadwal'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($alamat === '') { $errors[] = 'Alamat penjemputan wajib diisi.'; }
    if ($jadwal === '') { $errors[] = 'Jadwal penjemputan wajib diisi.'; }

    if (empty($errors)) {
        $stmt = mysqli_prepare($con, "INSERT INTO permintaan_jemput (user_id, alamat, jadwal, status, catatan) VALUES (?, ?, ?, 'Diminta', ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $uid, $alamat, $jadwal, $catatan);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Permintaan penjemputan berhasil dikirim. Petugas akan mencatat setoran setelah sampah dijemput.');
            header('Location: jemput.php');
            exit();
        } else {
            $errors[] = 'Gagal menyimpan permintaan.';
        }
    }
}

$stmt = mysqli_prepare($con, "SELECT * FROM permintaan_jemput WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
mysqli_stmt_close($stmt);

$page_title    = 'Minta Jemput';
$page_subtitle = 'Ajukan penjemputan sampah ke lokasi Anda.';
$active_menu   = 'jemput';
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Form Penjemputan</h2></div>
      <div class="card-body pt-3">
        <p class="text-muted small">
          Setoran akan dicatat oleh petugas setelah sampah dijemput dan ditimbang.
        </p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert-custom alert-custom-danger mb-3">
          <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
          <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" action="jemput.php">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label-custom" for="alamat">Alamat Penjemputan</label>
          <input type="text" class="form-control-custom" id="alamat" name="alamat" value="<?= e($_POST['alamat'] ?? '') ?>" placeholder="Alamat lengkap" required>
        </div>
        <div class="mb-3">
          <label class="form-label-custom" for="jadwal">Jadwal</label>
          <input type="datetime-local" class="form-control-custom" id="jadwal" name="jadwal" value="<?= e($_POST['jadwal'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label-custom" for="catatan">Catatan</label>
          <textarea class="form-control-custom" id="catatan" name="catatan" rows="2" placeholder="Catatan tambahan (opsional)"><?= e($_POST['catatan'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-dark-custom w-100"><i class="bi bi-send me-1"></i> Kirim Permintaan</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Riwayat Penjemputan</h2></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>#</th><th>Jadwal</th><th>Alamat</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="4" class="text-center text-muted-green py-4">Belum ada permintaan.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td class="table-order-id">#PJ-<?= e(str_pad($r['id'], 3, '0', STR_PAD_LEFT)) ?></td>
                <td><?= e(tanggal_waktu_id($r['jadwal'])) ?></td>
                <td><?= e(potong_teks($r['alamat'], 35)) ?><div class="table-user-sub"><?= e(potong_teks($r['catatan'] ?: '-', 40)) ?></div></td>
                <td><?= badge_jemput($r['status']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
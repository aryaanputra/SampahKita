<?php
/**
 * pengurus/penjemputan.php - Kelola penjemputan sampah
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus']);

$u   = current_user();
$uid = $u['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($aksi === 'jadwal') {
        $stmt = mysqli_prepare($con, "UPDATE permintaan_jemput SET status = 'Dijadwalkan', petugas_id = ? WHERE id = ? AND status = 'Diminta'");
        mysqli_stmt_bind_param($stmt, 'ii', $uid, $id);
        mysqli_stmt_execute($stmt);
        set_flash('info', 'Penjemputan dijadwalkan.');
        header('Location: penjemputan.php');
        exit();
    }
    if ($aksi === 'selesai') {
        $stmt = mysqli_prepare($con, "UPDATE permintaan_jemput SET status = 'Selesai', petugas_id = COALESCE(petugas_id, ?) WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $uid, $id);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Penjemputan ditandai selesai.');
        header('Location: penjemputan.php');
        exit();
    }
    if ($aksi === 'batal') {
        $stmt = mysqli_prepare($con, "UPDATE permintaan_jemput SET status = 'Dibatalkan' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        set_flash('warning', 'Penjemputan dibatalkan.');
        header('Location: penjemputan.php');
        exit();
    }
}

$filter = $_GET['status'] ?? '';
$where = '';
if (in_array($filter, ['Diminta', 'Dijadwalkan', 'Selesai', 'Dibatalkan'], true)) {
    $where = "WHERE pj.status = '" . mysqli_real_escape_string($con, $filter) . "'";
}

$rows = [];
$res = mysqli_query($con, "SELECT pj.*, u.name AS nasabah, u.no_hp, p.name AS petugas FROM permintaan_jemput pj JOIN users u ON u.id_user = pj.user_id LEFT JOIN users p ON p.id_user = pj.petugas_id $where ORDER BY pj.created_at DESC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; } }

$page_title    = 'Penjemputan';
$page_subtitle = 'Kelola permintaan penjemputan sampah dari nasabah.';
$active_menu   = 'penjemputan';
require __DIR__ . '/../includes/header.php';
?>

<div class="table-card-custom">
  <div class="table-header-control">
    <div class="table-search-box"><i class="bi bi-search table-search-icon"></i>
      <input type="text" class="table-search-input table-local-search" placeholder="Cari permintaan..."></div>
    <div class="table-filter-group">
      <form method="GET" action="penjemputan.php" class="d-flex">
        <select class="form-select-custom form-control-custom-sm" name="status" data-autosubmit>
          <option value="">Semua Status</option>
          <?php foreach (['Diminta','Dijadwalkan','Selesai','Dibatalkan'] as $st): ?>
            <option value="<?= e($st) ?>" <?= $filter === $st ? 'selected' : '' ?>><?= e($st) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table-custom">
      <thead><tr><th>#</th><th>Nasabah</th><th>Jadwal</th><th>Alamat / Estimasi</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="text-center text-muted-green py-4">Tidak ada data.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td class="table-order-id">#PJ-<?= e(str_pad($r['id'], 3, '0', STR_PAD_LEFT)) ?></td>
            <td>
              <div class="table-user-name"><?= e($r['nasabah']) ?></div>
              <div class="table-user-sub"><?= e($r['no_hp'] ?: '-') ?></div>
            </td>
            <td><?= e(tanggal_waktu_id($r['jadwal'])) ?></td>
            <td><?= e(potong_teks($r['alamat'], 40)) ?><div class="table-user-sub"><?= e($r['estimasi'] ?: '-') ?></div></td>
            <td><?= badge_jemput($r['status']) ?></td>
            <td class="text-center">
              <div class="table-actions justify-content-center">
                <?php if ($r['status'] === 'Diminta'): ?>
                  <form method="POST" class="d-inline" data-confirm="Jadwalkan penjemputan ini?"><?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="jadwal"><input type="hidden" name="id" value="<?= e($r['id']) ?>">
                    <button class="table-btn-action" title="Jadwalkan"><i class="bi bi-calendar-check"></i></button>
                  </form>
                <?php endif; ?>
                <?php if (in_array($r['status'], ['Diminta','Dijadwalkan'], true)): ?>
                  <form method="POST" class="d-inline" data-confirm="Tandai selesai?"><?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="selesai"><input type="hidden" name="id" value="<?= e($r['id']) ?>">
                    <button class="table-btn-action" title="Selesai"><i class="bi bi-check2"></i></button>
                  </form>
                  <form method="POST" class="d-inline" data-confirm="Batalkan permintaan ini?"><?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="batal"><input type="hidden" name="id" value="<?= e($r['id']) ?>">
                    <button class="table-btn-action delete" title="Batalkan"><i class="bi bi-x-lg"></i></button>
                  </form>
                <?php endif; ?>
                <?php if (in_array($r['status'], ['Selesai','Dibatalkan'], true)): ?>
                  <span class="text-muted-green" style="font-size:.8rem;"><?= e($r['petugas'] ?: '-') ?></span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * ketua/persetujuan.php - Persetujuan pencairan saldo
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['ketua']);

$u   = current_user();
$uid = $u['id_user'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');

    if (in_array($aksi, ['setujui', 'tolak'], true)) {
        $stmt = mysqli_prepare($con, "SELECT p.*, u.name AS nasabah FROM pencairan p JOIN users u ON u.id_user = p.user_id WHERE p.id = ? AND p.status = 'menunggu' LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $pc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$pc) {
            $errors[] = 'Pengajuan tidak valid atau sudah diproses.';
        } else {
            if ($aksi === 'setujui') {
                $saldo = get_saldo($con, (int) $pc['user_id']);
                if ((float) $pc['nominal'] > $saldo) {
                    $errors[] = 'Saldo nasabah tidak mencukupi (' . rupiah($saldo) . ').';
                }
            }
            if (empty($errors)) {
                $status = ($aksi === 'setujui') ? 'disetujui' : 'ditolak';
                $stmt = mysqli_prepare($con, "UPDATE pencairan SET status = ?, disetujui_oleh = ?, catatan_ketua = ?, tanggal_disetujui = NOW() WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'sisi', $status, $uid, $catatan, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                set_flash($aksi === 'setujui' ? 'success' : 'warning', 'Pencairan #PC-' . str_pad($id, 3, '0', STR_PAD_LEFT) . ' ' . ($aksi === 'setujui' ? 'disetujui.' : 'ditolak.'));
                header('Location: persetujuan.php');
                exit();
            }
        }
    }
}

$filter = $_GET['status'] ?? 'menunggu';
$allowed = ['menunggu', 'disetujui', 'ditolak', 'selesai'];
if (!in_array($filter, $allowed, true)) { $filter = 'menunggu'; }

$rows = [];
$stmt = mysqli_prepare($con, "SELECT p.*, u.name AS nasabah, u.no_hp, u.email FROM pencairan p JOIN users u ON u.id_user = p.user_id WHERE p.status = ? ORDER BY p.tanggal ASC");
mysqli_stmt_bind_param($stmt, 's', $filter);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
mysqli_stmt_close($stmt);

$page_title    = 'Persetujuan Pencairan';
$page_subtitle = 'Tinjau dan putuskan pengajuan pencairan nasabah.';
$active_menu   = 'persetujuan';
require __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert-custom alert-custom-danger mb-4">
    <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
    <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
  </div>
<?php endif; ?>

<div class="table-card-custom">
  <div class="table-header-control">
    <div class="table-search-box"><i class="bi bi-search table-search-icon"></i>
      <input type="text" class="table-search-input table-local-search" placeholder="Cari nasabah..."></div>
    <div class="table-filter-group">
      <form method="GET" action="persetujuan.php">
        <select class="form-control-custom form-control-custom-sm" name="status" data-autosubmit>
          <?php foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai'] as $k => $lbl): ?>
            <option value="<?= e($k) ?>" <?= $filter === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table-custom">
      <thead><tr><th>#</th><th>Nasabah</th><th>Tanggal</th><th>Nominal</th><th>Status</th><th class="text-center">Keputusan</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="text-center text-muted-green py-4">Tidak ada data pada status ini.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td class="table-order-id">#PC-<?= e(str_pad($r['id'], 3, '0', STR_PAD_LEFT)) ?></td>
            <td><div class="table-user-name"><?= e($r['nasabah']) ?></div><div class="table-user-sub"><?= e($r['email']) ?></div></td>
            <td><?= e(tanggal_waktu_id($r['tanggal'])) ?></td>
            <td class="table-amount"><?= e(rupiah($r['nominal'])) ?></td>
            <td><?= badge_pencairan($r['status']) ?></td>
            <td class="text-center">
              <?php if ($r['status'] === 'menunggu'): ?>
                <button type="button" class="btn btn-dark-custom btn-sm"
                  data-bs-toggle="modal" data-bs-target="#modalPutusan"
                  data-id="<?= e($r['id']) ?>" data-nama="<?= e($r['nasabah']) ?>" data-nominal="<?= e(rupiah($r['nominal'])) ?>">
                  <i class="bi bi-check2-circle me-1"></i> Putuskan
                </button>
              <?php else: ?>
                <span class="text-muted-green" style="font-size:.8rem;"><?= e($r['catatan_ketua'] ? potong_teks($r['catatan_ketua'], 30) : '-') ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal keputusan -->
<div class="modal fade" id="modalPutusan" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-xl);border:none;">
      <form method="POST" action="persetujuan.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="putusan-id">
        <div class="modal-header" style="border-bottom:1px solid var(--border-light);">
          <h5 class="modal-title">Putusan Pencairan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Nasabah <strong id="putusan-nama"></strong> mengajukan pencairan sebesar <strong id="putusan-nominal"></strong>.</p>
          <label class="form-label-custom">Catatan (opsional)</label>
          <textarea class="form-control-custom" name="catatan" rows="2" placeholder="Catatan untuk nasabah"></textarea>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border-light);">
          <button type="submit" name="aksi" value="tolak" class="btn" style="background:var(--sys-red-bg);color:var(--sys-red);font-weight:700;">
            <i class="bi bi-x-lg me-1"></i> Tolak
          </button>
          <button type="submit" name="aksi" value="setujui" class="btn btn-dark-custom">
            <i class="bi bi-check-lg me-1"></i> Setujui
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalPutusan');
    if (modal) {
      modal.addEventListener('show.bs.modal', function (ev) {
        var btn = ev.relatedTarget;
        if (!btn) { return; }
        document.getElementById('putusan-id').value = btn.getAttribute('data-id');
        document.getElementById('putusan-nama').textContent = btn.getAttribute('data-nama');
        document.getElementById('putusan-nominal').textContent = btn.getAttribute('data-nominal');
      });
    }
  });
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>

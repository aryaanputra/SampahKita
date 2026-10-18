<?php
/**
 * nasabah/pencairan.php - Ajukan & riwayat pencairan saldo
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['nasabah']);

$u   = current_user();
$uid = $u['id_user'];

$saldo  = get_saldo($con, $uid);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $nominal = (float) str_replace(['.', ','], ['', '.'], $_POST['nominal'] ?? '0');

    if ($nominal < 10000) {
        $errors[] = 'Minimal pencairan Rp 10.000.';
    } elseif ($nominal > $saldo) {
        $errors[] = 'Nominal melebihi saldo tersedia.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($con, "INSERT INTO pencairan (user_id, nominal, status, tanggal) VALUES (?, ?, 'menunggu', NOW())");
        mysqli_stmt_bind_param($stmt, 'id', $uid, $nominal);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Pengajuan pencairan dikirim. Menunggu persetujuan Ketua.');
            header('Location: pencairan.php');
            exit();
        }
        $errors[] = 'Gagal menyimpan pengajuan.';
    }
}

$stmt = mysqli_prepare($con, "SELECT * FROM pencairan WHERE user_id = ? ORDER BY tanggal DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
mysqli_stmt_close($stmt);

$page_title    = 'Ajukan Pencairan';
$page_subtitle = 'Tarik saldo tabungan Anda sesuai kebutuhan.';
$active_menu   = 'pencairan';
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card mb-4" style="background: linear-gradient(135deg, var(--brand-forest-medium), var(--brand-forest-dark)); color:#fff; border:none;">
      <div class="card-body">
        <span class="stat-label" style="color: rgba(255,255,255,.75);">Saldo Tersedia</span>
        <div class="stat-value" style="color:#fff; font-size:1.8rem; margin:.3rem 0;"><?= e(rupiah($saldo)) ?></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h2 class="card-title">Form Pencairan</h2></div>

      <?php if (!empty($errors)): ?>
        <div class="alert-custom alert-custom-danger mb-3">
          <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
          <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" action="pencairan.php" data-confirm="Ajukan pencairan ini?">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label-custom" for="nominal">Nominal (Rp)</label>
          <input type="number" class="form-control-custom" id="nominal" name="nominal" min="10000" step="1000" max="<?= e((int) $saldo) ?>" placeholder="mis. 50000" required>
          <small class="text-muted-green">Minimal Rp 10.000, maksimal sesuai saldo.</small>
        </div>
        <button type="submit" class="btn btn-dark-custom w-100" <?= $saldo < 10000 ? 'disabled' : '' ?>>
          <i class="bi bi-cash-coin me-1"></i> Ajukan Pencairan
        </button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Riwayat Pencairan</h2></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>#</th><th>Tanggal</th><th>Nominal</th><th>Status</th><th>Catatan</th></tr></thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada pengajuan.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td class="table-order-id">#PC-<?= e(str_pad($r['id'], 3, '0', STR_PAD_LEFT)) ?></td>
                <td><?= e(tanggal_waktu_id($r['tanggal'])) ?></td>
                <td class="table-amount"><?= e(rupiah($r['nominal'])) ?></td>
                <td><?= badge_pencairan($r['status']) ?></td>
                <td><?= e(potong_teks($r['catatan_ketua'] ?: '-', 40)) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

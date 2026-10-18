<?php
/**
 * nasabah/saldo.php - Saldo & riwayat mutasi
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

$saldo = get_saldo($con, $uid);

$stmt = mysqli_prepare($con, "SELECT COALESCE(SUM(CASE WHEN tipe='kredit' THEN nominal ELSE 0 END),0) AS kredit, COALESCE(SUM(CASE WHEN tipe='debit' THEN nominal ELSE 0 END),0) AS debit FROM saldo_mutasi WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$sum = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($con, "SELECT * FROM saldo_mutasi WHERE user_id = ? ORDER BY tanggal DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
mysqli_stmt_close($stmt);

$page_title    = 'Saldo & Riwayat';
$page_subtitle = 'Rincian seluruh transaksi saldo tabungan Anda.';
$active_menu   = 'saldo';
$page_actions  = '<button type="button" class="btn btn-dark-custom" data-print><i class="bi bi-printer me-1"></i> Cetak</button>';
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4 mb-1">
  <div class="col-md-4">
    <div class="card"><div class="card-body stat-mini">
      <div class="stat-mini-icon"><i class="bi bi-wallet2"></i></div>
      <div><div class="stat-mini-label">Saldo Saat Ini</div><div class="stat-mini-value"><?= e(rupiah($saldo)) ?></div></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body stat-mini">
      <div class="stat-mini-icon"><i class="bi bi-arrow-down-circle"></i></div>
      <div><div class="stat-mini-label">Total Kredit</div><div class="stat-mini-value"><?= e(rupiah($sum['kredit'])) ?></div></div>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card"><div class="card-body stat-mini">
      <div class="stat-mini-icon"><i class="bi bi-arrow-up-circle"></i></div>
      <div><div class="stat-mini-label">Total Debit</div><div class="stat-mini-value"><?= e(rupiah($sum['debit'])) ?></div></div>
    </div></div>
  </div>
</div>

<div class="table-card-custom no-print-controls">
  <div class="table-header-control"><h2 class="card-title mb-0">Mutasi Saldo</h2></div>
  <div class="table-responsive">
    <table class="table-custom">
      <thead><tr><th>Tanggal</th><th>Keterangan</th><th>Tipe</th><th>Nominal</th><th class="text-end">Saldo Akhir</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada mutasi.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?= e(tanggal_waktu_id($r['tanggal'])) ?></td>
            <td><?= e($r['keterangan'] ?: '-') ?></td>
            <td><?= $r['tipe'] === 'kredit' ? '<span class="badge-table success">Kredit</span>' : '<span class="badge-table failed">Debit</span>' ?></td>
            <td class="table-amount"><?= e(rupiah($r['nominal'])) ?></td>
            <td class="table-amount text-end"><?= e(rupiah($r['saldo_akhir'])) ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

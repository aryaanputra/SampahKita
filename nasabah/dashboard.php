<?php
/**
 * nasabah/dashboard.php - Dasbor saldo & ringkasan nasabah
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['nasabah']);

$u   = current_user();
$uid = $u['id_user'];

$saldo = get_saldo($con, $uid);

// Total setoran (nilai)
$stmt = mysqli_prepare($con, "SELECT COALESCE(SUM(total_nilai),0) AS total, COUNT(*) AS jumlah FROM setoran WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
$total_setoran = (float) $row['total'];
$jumlah_setoran = (int) $row['jumlah'];

// Total berat
$stmt = mysqli_prepare($con, "SELECT COALESCE(SUM(d.berat_kg),0) AS total FROM detail_setoran d JOIN setoran s ON s.id = d.setoran_id WHERE s.user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
$total_berat = (float) $row['total'];

// Pencairan menunggu
$stmt = mysqli_prepare($con, "SELECT COUNT(*) AS c FROM pencairan WHERE user_id = ? AND status = 'menunggu'");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
$pencairan_menunggu = (int) $row['c'];

// Setoran terakhir
$stmt = mysqli_prepare($con, "SELECT s.id, s.tanggal, s.total_nilai, u.name AS petugas FROM setoran s LEFT JOIN users u ON u.id_user = s.petugas_id WHERE s.user_id = ? ORDER BY s.tanggal DESC LIMIT 5");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$setoran_list = mysqli_stmt_get_result($stmt);
$setoran_rows = [];
while ($r = mysqli_fetch_assoc($setoran_list)) { $setoran_rows[] = $r; }
mysqli_stmt_close($stmt);

// Mutasi untuk grafik
$stmt = mysqli_prepare($con, "SELECT tanggal, saldo_akhir FROM saldo_mutasi WHERE user_id = ? ORDER BY tanggal ASC LIMIT 30");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$mut = mysqli_stmt_get_result($stmt);
$chart_labels = [];
$chart_data = [];
while ($r = mysqli_fetch_assoc($mut)) {
    $chart_labels[] = date('d/m', strtotime($r['tanggal']));
    $chart_data[] = (float) $r['saldo_akhir'];
}
mysqli_stmt_close($stmt);

$page_title    = 'Dasbor Saldo';
$page_subtitle = 'Selamat datang kembali, ' . $u['name'] . '.';
$active_menu   = 'dashboard';
$page_actions  = '<a href="setor.php" class="btn btn-dark-custom"><i class="bi bi-plus-lg me-1"></i> Setor Sampah</a>';
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card h-100" style="background: linear-gradient(135deg, var(--brand-forest-medium), var(--brand-forest-dark)); color:#fff; border:none;">
      <div class="card-body">
        <span class="stat-label" style="color: rgba(255,255,255,.75);">Saldo Tabungan</span>
        <div class="stat-value" style="color:#fff; font-size: 2rem; margin:.35rem 0;"><?= e(rupiah($saldo)) ?></div>
        <p class="mb-4" style="color: rgba(255,255,255,.75); font-size:.85rem;">Saldo dapat dicairkan melalui menu Ajukan Pencairan.</p>
        <a href="pencairan.php" class="btn btn-sm" style="background: var(--brand-lime); color: var(--brand-forest-dark); font-weight:700;">
          <i class="bi bi-cash-coin me-1"></i> Ajukan Pencairan
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="row g-4">
      <div class="col-sm-6">
        <div class="card h-100"><div class="card-body stat-mini">
          <div class="stat-mini-icon"><i class="bi bi-arrow-up-circle"></i></div>
          <div><div class="stat-mini-label">Total Nilai Setoran</div><div class="stat-mini-value"><?= e(rupiah($total_setoran)) ?></div></div>
        </div></div>
      </div>
      <div class="col-sm-6">
        <div class="card h-100"><div class="card-body stat-mini">
          <div class="stat-mini-icon"><i class="bi bi-speedometer2"></i></div>
          <div><div class="stat-mini-label">Total Berat</div><div class="stat-mini-value"><?= e(kg($total_berat)) ?></div></div>
        </div></div>
      </div>
      <div class="col-sm-6">
        <div class="card h-100"><div class="card-body stat-mini">
          <div class="stat-mini-icon"><i class="bi bi-basket"></i></div>
          <div><div class="stat-mini-label">Jumlah Setoran</div><div class="stat-mini-value"><?= e($jumlah_setoran) ?> kali</div></div>
        </div></div>
      </div>
      <div class="col-sm-6">
        <div class="card h-100"><div class="card-body stat-mini">
          <div class="stat-mini-icon"><i class="bi bi-hourglass-split"></i></div>
          <div><div class="stat-mini-label">Pencairan Menunggu</div><div class="stat-mini-value"><?= e($pencairan_menunggu) ?></div></div>
        </div></div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Grafik Saldo</h2></div>
      <div id="saldo-chart"></div>
    </div>
  </div>

  <div class="col-12">
    <div class="table-card-custom">
      <div class="table-header-control">
        <h2 class="card-title mb-0">Setoran Terakhir</h2>
        <div class="table-filter-group">
          <a href="saldo.php" class="btn-table-action"><i class="bi bi-clock-history"></i> Lihat Riwayat</a>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr><th>#</th><th>Tanggal</th><th>Petugas</th><th>Nilai</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($setoran_rows)): ?>
              <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada setoran.</td></tr>
            <?php else: foreach ($setoran_rows as $s): ?>
              <tr>
                <td class="table-order-id">#ST-<?= e(str_pad($s['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                <td><?= e(tanggal_waktu_id($s['tanggal'])) ?></td>
                <td><?= e($s['petugas'] ?: '-') ?></td>
                <td class="table-amount"><?= e(rupiah($s['total_nilai'])) ?></td>
                <td class="text-end"><a href="setor.php?id=<?= e($s['id']) ?>" class="table-btn-action" title="Detail"><i class="bi bi-eye"></i></a></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  window.__saldoChart = {
    labels: <?= json_encode($chart_labels) ?>,
    data: <?= json_encode($chart_data) ?>
  };
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>

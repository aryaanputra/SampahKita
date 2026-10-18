<?php
/**
 * pengurus/pencairan.php - Proses pencairan yang telah disetujui Ketua
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus']);

$u   = current_user();
$uid = $u['id_user'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($aksi === 'selesai') {
        $stmt = mysqli_prepare($con, "SELECT * FROM pencairan WHERE id = ? AND status = 'disetujui' LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $pc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$pc) {
            $errors[] = 'Data pencairan tidak valid atau belum disetujui.';
        } else {
            $user_id = (int) $pc['user_id'];
            $nominal = (float) $pc['nominal'];
            $prev = get_saldo($con, $user_id);
            if ($nominal > $prev) {
                $errors[] = 'Saldo nasabah tidak mencukupi untuk pencairan ini.';
            } else {
                mysqli_begin_transaction($con);
                try {
                    $after = $prev - $nominal;
                    $stmt = mysqli_prepare($con, "INSERT INTO saldo_mutasi (user_id, tipe, nominal, saldo_akhir, ref_type, ref_id, keterangan, tanggal) VALUES (?, 'debit', ?, ?, 'pencairan', ?, 'Pencairan saldo', NOW())");
                    mysqli_stmt_bind_param($stmt, 'iddi', $user_id, $nominal, $after, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $stmt = mysqli_prepare($con, "UPDATE pencairan SET status = 'selesai', diproses_oleh = ?, tanggal_selesai = NOW() WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'ii', $uid, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    mysqli_commit($con);
                    set_flash('success', 'Pencairan ditandai selesai dan saldo telah dikurangi.');
                } catch (Throwable $ex) {
                    mysqli_rollback($con);
                    $errors[] = 'Gagal memproses pencairan: ' . $ex->getMessage();
                }
                if (empty($errors)) { header('Location: pencairan.php'); exit(); }
            }
        }
    }
}

$filter = $_GET['status'] ?? '';
$where = '';
if (in_array($filter, ['menunggu', 'disetujui', 'ditolak', 'selesai'], true)) {
    $where = "WHERE p.status = '" . mysqli_real_escape_string($con, $filter) . "'";
}

$rows = [];
$res = mysqli_query($con, "SELECT p.*, u.name AS nasabah, u.no_hp, k.name AS ketua FROM pencairan p JOIN users u ON u.id_user = p.user_id LEFT JOIN users k ON k.id_user = p.disetujui_oleh $where ORDER BY p.status = 'disetujui' DESC, p.tanggal DESC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; } }

// Ringkasan
$sum = ['menunggu' => 0, 'disetujui' => 0, 'selesai' => 0];
$res = mysqli_query($con, "SELECT status, COUNT(*) c, COALESCE(SUM(nominal),0) v FROM pencairan GROUP BY status");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $sum[$r['status']] = ['c' => (int) $r['c'], 'v' => (float) $r['v']]; } }

$page_title    = 'Pencairan';
$page_subtitle = 'Proses pencairan saldo yang telah disetujui Ketua.';
$active_menu   = 'pencairan';
require __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert-custom alert-custom-danger mb-4">
    <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
    <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
  </div>
<?php endif; ?>

<div class="row g-4 mb-1">
  <div class="col-md-4"><div class="card card-stat"><div class="card-header"><span class="stat-label">Menunggu Persetujuan</span></div>
    <div class="stat-value"><?= e($sum['menunggu']['c'] ?? 0) ?></div><div class="trend-badge trend-up"><span>Perlu keputusan Ketua</span></div></div></div>
  <div class="col-md-4"><div class="card card-stat"><div class="card-header"><span class="stat-label">Siap Diproses</span></div>
    <div class="stat-value"><?= e($sum['disetujui']['c'] ?? 0) ?></div><div class="trend-badge trend-up"><i class="bi bi-check2-circle"></i><span>Telah disetujui Ketua</span></div></div></div>
  <div class="col-md-4"><div class="card card-stat"><div class="card-header"><span class="stat-label">Total Selesai</span></div>
    <div class="stat-value"><?= e(rupiah($sum['selesai']['v'] ?? 0)) ?></div><div class="trend-badge trend-up"><i class="bi bi-wallet2"></i><span><?= e($sum['selesai']['c'] ?? 0) ?> transaksi</span></div></div></div>
</div>

<div class="table-card-custom">
  <div class="table-header-control">
    <div class="table-search-box"><i class="bi bi-search table-search-icon"></i>
      <input type="text" class="table-search-input table-local-search" placeholder="Cari pencairan..."></div>
    <div class="table-filter-group">
      <form method="GET" action="pencairan.php">
        <select class="form-control-custom form-control-custom-sm" name="status" data-autosubmit>
          <option value="">Semua Status</option>
          <?php foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai'] as $k => $lbl): ?>
            <option value="<?= e($k) ?>" <?= $filter === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table-custom">
      <thead><tr><th>#</th><th>Nasabah</th><th>Tanggal</th><th>Nominal</th><th>Status</th><th>Catatan Ketua</th><th class="text-center">Aksi</th></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" class="text-center text-muted-green py-4">Tidak ada data.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td class="table-order-id">#PC-<?= e(str_pad($r['id'], 3, '0', STR_PAD_LEFT)) ?></td>
            <td><div class="table-user-name"><?= e($r['nasabah']) ?></div><div class="table-user-sub"><?= e($r['no_hp'] ?: '-') ?></div></td>
            <td><?= e(tanggal_waktu_id($r['tanggal'])) ?></td>
            <td class="table-amount"><?= e(rupiah($r['nominal'])) ?></td>
            <td><?= badge_pencairan($r['status']) ?></td>
            <td><?= e($r['catatan_ketua'] ? potong_teks($r['catatan_ketua'], 35) : '-') ?></td>
            <td class="text-center">
              <?php if ($r['status'] === 'disetujui'): ?>
                <form method="POST" class="d-inline" data-confirm="Proses pencairan <?= e($r['nasabah']) ?> sebesar <?= e(rupiah($r['nominal'])) ?>?"><?= csrf_field() ?>
                  <input type="hidden" name="aksi" value="selesai"><input type="hidden" name="id" value="<?= e($r['id']) ?>">
                  <button class="btn btn-dark-custom btn-sm"><i class="bi bi-cash-coin me-1"></i> Bayar</button>
                </form>
              <?php elseif ($r['status'] === 'menunggu'): ?>
                <span class="text-muted-green" style="font-size:.8rem;">Menunggu Ketua</span>
              <?php else: ?>
                <span class="text-muted-green" style="font-size:.8rem;"><?= e($r['tanggal_selesai'] ? tanggal_id($r['tanggal_selesai']) : '-') ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

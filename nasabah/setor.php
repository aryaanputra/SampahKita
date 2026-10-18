<?php
/**
 * nasabah/setor.php - Informasi setor & riwayat setoran nasabah
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['nasabah']);

$u   = current_user();
$uid = $u['id_user'];

$detail = null;
$detail_items = [];
if (isset($_GET['id'])) {
    $sid = (int) $_GET['id'];
    $stmt = mysqli_prepare($con, "SELECT s.*, u.name AS petugas FROM setoran s LEFT JOIN users u ON u.id_user = s.petugas_id WHERE s.id = ? AND s.user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $sid, $uid);
    mysqli_stmt_execute($stmt);
    $detail = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($detail) {
        $stmt = mysqli_prepare($con, "SELECT d.*, j.nama, j.kategori FROM detail_setoran d JOIN jenis_sampah j ON j.id = d.jenis_id WHERE d.setoran_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $sid);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) { $detail_items[] = $r; }
        mysqli_stmt_close($stmt);
    }
}

if (!$detail) {
    $stmt = mysqli_prepare($con, "SELECT s.id, s.tanggal, s.total_nilai, s.catatan FROM setoran s WHERE s.user_id = ? ORDER BY s.tanggal DESC");
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $setoran_rows = [];
    while ($r = mysqli_fetch_assoc($res)) { $setoran_rows[] = $r; }
    mysqli_stmt_close($stmt);
}

$harga_list = mysqli_query($con, "SELECT * FROM jenis_sampah ORDER BY kategori, nama");

$page_title    = $detail ? 'Detail Setoran' : 'Setor Sampah';
$page_subtitle = $detail ? 'Rincian timbangan setoran sampah Anda.' : 'Daftar harga sampah dan riwayat setoran Anda.';
$active_menu   = 'setor';
if ($detail) {
    $page_actions = '<a href="setor.php" class="btn btn-dark-custom"><i class="bi bi-arrow-left me-1"></i> Kembali</a>';
}
require __DIR__ . '/../includes/header.php';
?>

<?php if ($detail): ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="table-card-custom">
        <div class="table-header-control">
          <h2 class="card-title mb-0">Setoran #ST-<?= e(str_pad($detail['id'], 4, '0', STR_PAD_LEFT)) ?></h2>
          <span class="badge-table success">Selesai</span>
        </div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>Jenis</th><th>Kategori</th><th>Berat</th><th>Harga/kg</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($detail_items as $it): ?>
                <tr>
                  <td class="table-product-name"><?= e($it['nama']) ?></td>
                  <td><?= e($it['kategori']) ?></td>
                  <td><?= e(kg($it['berat_kg'])) ?></td>
                  <td><?= e(rupiah($it['harga_per_kg'])) ?></td>
                  <td class="table-amount text-end"><?= e(rupiah($it['subtotal'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="total-bar mt-3"><span>Total</span><span class="total-value"><?= e(rupiah($detail['total_nilai'])) ?></span></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header"><h2 class="card-title">Informasi</h2></div>
        <ul class="clean-list">
          <li class="clean-list-item"><i class="bi bi-calendar-event"></i><div><div class="stat-mini-label">Tanggal</div><div><?= e(tanggal_waktu_id($detail['tanggal'])) ?></div></div></li>
          <li class="clean-list-item"><i class="bi bi-person-badge"></i><div><div class="stat-mini-label">Petugas</div><div><?= e($detail['petugas'] ?: '-') ?></div></div></li>
          <li class="clean-list-item"><i class="bi bi-journal-text"></i><div><div class="stat-mini-label">Catatan</div><div><?= e($detail['catatan'] ?: '-') ?></div></div></li>
        </ul>
      </div>
    </div>
  </div>

<?php else: ?>
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header"><h2 class="card-title">Cara Menyetor Sampah</h2></div>
        <ul class="clean-list">
          <li class="clean-list-item"><i class="bi bi-1-circle-fill"></i><div>Pilah sampah anorganik yang masih bernilai (plastik, kertas, kaleng, kaca, logam).</div></li>
          <li class="clean-list-item"><i class="bi bi-2-circle-fill"></i><div>Bawa ke kantor Bank Sampah atau ajukan <a href="jemput.php">penjemputan</a>.</div></li>
          <li class="clean-list-item"><i class="bi bi-3-circle-fill"></i><div>Petugas akan menimbang dan mencatat setoran Anda.</div></li>
          <li class="clean-list-item"><i class="bi bi-4-circle-fill"></i><div>Saldo otomatis bertambah dan dapat dicairkan.</div></li>
        </ul>
      </div>

      <div class="table-card-custom mt-4">
        <div class="table-header-control"><h2 class="card-title mb-0">Daftar Harga</h2></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>Jenis</th><th>Kategori</th><th class="text-end">Harga/kg</th></tr></thead>
            <tbody>
              <?php while ($h = mysqli_fetch_assoc($harga_list)): ?>
                <tr>
                  <td class="table-product-name"><?= e($h['nama']) ?></td>
                  <td><?= e($h['kategori']) ?></td>
                  <td class="table-amount text-end"><?= e(rupiah($h['harga_per_kg'])) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="table-card-custom">
        <div class="table-header-control"><h2 class="card-title mb-0">Riwayat Setoran</h2></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>#</th><th>Tanggal</th><th>Catatan</th><th>Nilai</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
              <?php if (empty($setoran_rows)): ?>
                <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada setoran.</td></tr>
              <?php else: foreach ($setoran_rows as $s): ?>
                <tr>
                  <td class="table-order-id">#ST-<?= e(str_pad($s['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                  <td><?= e(tanggal_waktu_id($s['tanggal'])) ?></td>
                  <td><?= e(potong_teks($s['catatan'] ?: '-', 40)) ?></td>
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
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>

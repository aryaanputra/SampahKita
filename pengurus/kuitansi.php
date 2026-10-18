<?php
/**
 * pengurus/kuitansi.php - Kuitansi setoran (dapat dicetak)
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus', 'ketua']);

$id = (int) ($_GET['id'] ?? 0);
$setoran = null;
$items = [];

$stmt = mysqli_prepare($con, "SELECT s.*, u.name AS nasabah, u.email AS nasabah_email, u.no_hp AS nasabah_hp, p.name AS petugas FROM setoran s JOIN users u ON u.id_user = s.user_id LEFT JOIN users p ON p.id_user = s.petugas_id WHERE s.id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$setoran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$setoran) {
    set_flash('danger', 'Setoran tidak ditemukan.');
    header('Location: timbang.php');
    exit();
}

$stmt = mysqli_prepare($con, "SELECT d.*, j.nama, j.kategori FROM detail_setoran d JOIN jenis_sampah j ON j.id = d.jenis_id WHERE d.setoran_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($res)) { $items[] = $r; }
mysqli_stmt_close($stmt);

$total_berat = 0.0;
foreach ($items as $it) { $total_berat += (float) $it['berat_kg']; }

$page_title    = 'Kuitansi Setoran';
$page_subtitle = 'Bukti setoran sampah nasabah.';
$active_menu   = 'timbang';
$page_actions  = '<button type="button" class="btn btn-dark-custom no-print" data-print><i class="bi bi-printer me-1"></i> Cetak</button>';
require __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="kuitansi-box">
      <div class="kuitansi-head">
        <div>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-recycle" style="font-size:1.8rem;color:var(--brand-forest-medium);"></i>
            <div>
              <div style="font-weight:800;font-size:1.1rem;">Bank Sampah Kali Laya Berseri</div>
              <div class="text-muted-green" style="font-size:.85rem;">Kuitansi Setoran Sampah</div>
            </div>
          </div>
        </div>
        <div class="text-end">
          <div class="stat-mini-label">No. Kuitansi</div>
          <div style="font-weight:800;">#ST-<?= e(str_pad($setoran['id'], 4, '0', STR_PAD_LEFT)) ?></div>
          <div class="text-muted-green" style="font-size:.85rem;"><?= e(tanggal_waktu_id($setoran['tanggal'])) ?></div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-sm-6">
          <div class="stat-mini-label">Nasabah</div>
          <div style="font-weight:700;"><?= e($setoran['nasabah']) ?></div>
          <div class="text-muted-green" style="font-size:.85rem;"><?= e($setoran['nasabah_email']) ?> &middot; <?= e($setoran['nasabah_hp'] ?: '-') ?></div>
        </div>
        <div class="col-sm-6 text-sm-end">
          <div class="stat-mini-label">Petugas</div>
          <div style="font-weight:700;"><?= e($setoran['petugas'] ?: '-') ?></div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Jenis</th><th>Kategori</th><th>Berat</th><th>Harga/kg</th><th class="text-end">Subtotal</th></tr></thead>
          <tbody>
            <?php foreach ($items as $it): ?>
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

      <div class="total-bar mt-3">
        <span>Total (<?= e(kg($total_berat)) ?>)</span>
        <span class="total-value"><?= e(rupiah($setoran['total_nilai'])) ?></span>
      </div>

      <?php if (!empty($setoran['catatan'])): ?>
        <p class="text-muted-green mt-3 mb-0" style="font-size:.9rem;"><strong>Catatan:</strong> <?= e($setoran['catatan']) ?></p>
      <?php endif; ?>

      <div class="row mt-5">
        <div class="col-6 text-center">
          <div class="text-muted-green" style="font-size:.85rem;">Nasabah</div>
          <div style="margin-top:3.5rem;border-top:1px solid var(--border-light);padding-top:.35rem;"><?= e($setoran['nasabah']) ?></div>
        </div>
        <div class="col-6 text-center">
          <div class="text-muted-green" style="font-size:.85rem;">Petugas</div>
          <div style="margin-top:3.5rem;border-top:1px solid var(--border-light);padding-top:.35rem;"><?= e($setoran['petugas'] ?: '................') ?></div>
        </div>
      </div>
    </div>

    <div class="text-center mt-4 no-print">
      <a href="timbang.php" class="btn" style="background:var(--bs-body-bg);color:var(--text-main);"><i class="bi bi-arrow-left me-1"></i> Kembali ke Timbang</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

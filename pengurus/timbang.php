<?php
/**
 * pengurus/timbang.php - Timbang & catat setoran nasabah
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus']);

$u    = current_user();
$uid  = $u['id_user'];
$errors = [];

$jenis_list = [];
$res = mysqli_query($con, "SELECT * FROM jenis_sampah ORDER BY nama ASC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $jenis_list[] = $r; } }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $user_id = (int) ($_POST['user_id'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');
    $ids  = $_POST['jenis_id'] ?? [];
    $berats = $_POST['berat'] ?? [];

    if ($user_id <= 0) { $errors[] = 'Pilih nasabah terlebih dahulu.'; }

    // Kumpulkan item valid + harga dari DB (bukan dari input)
    $items = [];
    if (is_array($ids) && is_array($berats)) {
        foreach ($ids as $i => $jid) {
            $jid = (int) $jid;
            $w = (float) str_replace(',', '.', $berats[$i] ?? '0');
            if ($jid <= 0 || $w <= 0) { continue; }

            $q = mysqli_prepare($con, "SELECT harga_per_kg FROM jenis_sampah WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($q, 'i', $jid);
            mysqli_stmt_execute($q);
            $jr = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
            mysqli_stmt_close($q);
            if (!$jr) { continue; }

            $harga = (float) $jr['harga_per_kg'];
            $items[] = [
                'jenis_id' => $jid,
                'berat'    => $w,
                'harga'    => $harga,
                'subtotal' => $w * $harga,
            ];
        }
    }

    if (empty($items)) { $errors[] = 'Isi minimal satu baris timbangan dengan berat lebih dari 0.'; }

    if (empty($errors)) {
        $total = 0.0;
        foreach ($items as $it) { $total += $it['subtotal']; }

        mysqli_begin_transaction($con);
        $ok = false;
        try {
            $stmt = mysqli_prepare($con, "INSERT INTO setoran (user_id, tanggal, total_nilai, petugas_id, catatan) VALUES (?, NOW(), ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iids', $user_id, $total, $uid, $catatan);
            mysqli_stmt_execute($stmt);
            $setoran_id = mysqli_insert_id($con);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($con, "INSERT INTO detail_setoran (setoran_id, jenis_id, berat_kg, harga_per_kg, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmtStok = mysqli_prepare($con, "UPDATE jenis_sampah SET stok_kg = stok_kg + ? WHERE id = ?");
            foreach ($items as $it) {
                mysqli_stmt_bind_param($stmt, 'iiddd', $setoran_id, $it['jenis_id'], $it['berat'], $it['harga'], $it['subtotal']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_param($stmtStok, 'di', $it['berat'], $it['jenis_id']);
                mysqli_stmt_execute($stmtStok);
            }
            mysqli_stmt_close($stmt);
            mysqli_stmt_close($stmtStok);

            // Mutasi saldo (kredit) + saldo_akhir
            $prev = 0.0;
            $q = mysqli_query($con, "SELECT saldo_akhir FROM saldo_mutasi WHERE user_id = " . $user_id . " ORDER BY id DESC LIMIT 1");
            if ($q && ($pr = mysqli_fetch_assoc($q))) { $prev = (float) $pr['saldo_akhir']; }
            $after = $prev + $total;

            $stmt = mysqli_prepare($con, "INSERT INTO saldo_mutasi (user_id, tipe, nominal, saldo_akhir, ref_type, ref_id, keterangan, tanggal) VALUES (?, 'kredit', ?, ?, 'setoran', ?, 'Setoran sampah', NOW())");
            mysqli_stmt_bind_param($stmt, 'iddi', $user_id, $total, $after, $setoran_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($con);
            $ok = true;
        } catch (Throwable $ex) {
            mysqli_rollback($con);
            $errors[] = 'Gagal mencatat setoran: ' . $ex->getMessage();
        }

        if ($ok) {
            set_flash('success', 'Setoran dicatat (nilai ' . rupiah($total) . ').');
            header('Location: kuitansi.php?id=' . $setoran_id);
            exit();
        }
    }
}

// Riwayat setoran terakhir
$setoran_list = [];
$res = mysqli_query($con, "SELECT s.id, s.tanggal, s.total_nilai, u.name AS nasabah FROM setoran s JOIN users u ON u.id_user = s.user_id ORDER BY s.tanggal DESC LIMIT 8");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $setoran_list[] = $r; } }

$nasabah_list = [];
$res = mysqli_query($con, "SELECT id_user, name FROM users WHERE role = 'nasabah' AND aktif = 1 ORDER BY name ASC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $nasabah_list[] = $r; } }

$page_title    = 'Timbang & Catat';
$page_subtitle = 'Catat timbangan setoran sampah nasabah.';
$active_menu   = 'timbang';
require __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert-custom alert-custom-danger mb-4">
    <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
    <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Form Timbangan</h2></div>

      <form method="POST" action="timbang.php" data-confirm="Simpan setoran ini dan tambahkan ke saldo nasabah?">
        <?= csrf_field() ?>

        <div class="row g-3 mb-3">
          <div class="col-md-7">
            <label class="form-label-custom">Nasabah</label>
            <select class="form-select-custom" name="user_id" required>
              <option value="">-- Pilih nasabah --</option>
              <?php foreach ($nasabah_list as $n): ?>
                <option value="<?= e($n['id_user']) ?>" <?= ((int)($_POST['user_id'] ?? 0) === (int)$n['id_user']) ? 'selected' : '' ?>><?= e($n['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label-custom">Catatan</label>
            <input type="text" class="form-control-custom" name="catatan" value="<?= e($_POST['catatan'] ?? '') ?>" placeholder="mis. Setoran kantor">
          </div>
        </div>

        <label class="form-label-custom">Rincian Timbangan</label>
        <div id="items-wrap">
          <?php
          // Baris awal (satu baris kosong) — dirender langsung agar tanpa JS pun berguna.
          $pre_ids = $_POST['jenis_id'] ?? [0];
          $pre_berats = $_POST['berat'] ?? [''];
          $count = max(1, count($pre_ids));
          for ($i = 0; $i < $count; $i++):
              $sel = (int) ($pre_ids[$i] ?? 0);
              $w = $pre_berats[$i] ?? '';
          ?>
          <div class="item-row">
            <div>
              <label class="form-label-custom">Jenis Sampah</label>
              <select class="form-select-custom" name="jenis_id[]" required>
                <option value="">-- Pilih jenis --</option>
                <?php foreach ($jenis_list as $j): ?>
                  <option value="<?= e($j['id']) ?>" data-harga="<?= e($j['harga_per_kg']) ?>" <?= $sel === (int)$j['id'] ? 'selected' : '' ?>>
                    <?= e($j['nama']) ?> (<?= e(rupiah($j['harga_per_kg'])) ?>/kg)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label-custom">Berat (kg)</label>
              <input type="number" step="0.01" min="0" class="form-control-custom input-berat" name="berat[]" value="<?= e($w) ?>" placeholder="0.00" required>
            </div>
            <div>
              <label class="form-label-custom">Harga/kg</label>
              <input type="text" class="form-control-custom input-harga" readonly tabindex="-1" value="">
            </div>
            <div>
              <label class="form-label-custom">Subtotal</label>
              <div class="form-control-custom item-subtotal" style="background:var(--bs-body-bg);">Rp 0</div>
            </div>
            <div>
              <label class="form-label-custom d-none d-md-block">&nbsp;</label>
              <button type="button" class="btn item-row-remove" title="Hapus baris" style="background:var(--sys-red-bg);color:var(--sys-red);"><i class="bi bi-dash-lg"></i></button>
            </div>
          </div>
          <?php endfor; ?>
        </div>

        <template id="item-row-template">
          <div class="item-row">
            <div>
              <label class="form-label-custom">Jenis Sampah</label>
              <select class="form-select-custom" name="jenis_id[]" required>
                <option value="">-- Pilih jenis --</option>
                <?php foreach ($jenis_list as $j): ?>
                  <option value="<?= e($j['id']) ?>" data-harga="<?= e($j['harga_per_kg']) ?>"><?= e($j['nama']) ?> (<?= e(rupiah($j['harga_per_kg'])) ?>/kg)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label-custom">Berat (kg)</label>
              <input type="number" step="0.01" min="0" class="form-control-custom input-berat" name="berat[]" placeholder="0.00" required>
            </div>
            <div>
              <label class="form-label-custom">Harga/kg</label>
              <input type="text" class="form-control-custom input-harga" readonly tabindex="-1" value="">
            </div>
            <div>
              <label class="form-label-custom">Subtotal</label>
              <div class="form-control-custom item-subtotal" style="background:var(--bs-body-bg);">Rp 0</div>
            </div>
            <div>
              <label class="form-label-custom d-none d-md-block">&nbsp;</label>
              <button type="button" class="btn item-row-remove" title="Hapus baris" style="background:var(--sys-red-bg);color:var(--sys-red);"><i class="bi bi-dash-lg"></i></button>
            </div>
          </div>
        </template>

        <div class="d-flex align-items-center gap-2 mt-1">
          <button type="button" class="btn btn-dark-custom" id="add-item"><i class="bi bi-plus-lg me-1"></i> Tambah Baris</button>
          <span class="text-muted-green" style="font-size:.85rem;">Harga diambil otomatis dari daftar jenis sampah.</span>
        </div>

        <div class="total-bar mt-4">
          <span>Total Nilai Setoran</span>
          <span class="total-value" id="grand-total">Rp 0</span>
        </div>

        <button type="submit" class="btn btn-dark-custom w-100 mt-3"><i class="bi bi-check2-circle me-1"></i> Simpan Setoran</button>
      </form>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Setoran Terakhir</h2></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>#</th><th>Nasabah</th><th>Tanggal</th><th class="text-end">Nilai</th><th></th></tr></thead>
          <tbody>
            <?php if (empty($setoran_list)): ?>
              <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada setoran.</td></tr>
            <?php else: foreach ($setoran_list as $s): ?>
              <tr>
                <td class="table-order-id">#ST-<?= e(str_pad($s['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                <td><?= e($s['nasabah']) ?><div class="table-user-sub"><?= e(tanggal_id($s['tanggal'])) ?></div></td>
                <td><?= e(date('H:i', strtotime($s['tanggal']))) ?></td>
                <td class="table-amount text-end"><?= e(rupiah($s['total_nilai'])) ?></td>
                <td class="text-end"><a href="kuitansi.php?id=<?= e($s['id']) ?>" class="table-btn-action" title="Kuitansi"><i class="bi bi-printer"></i></a></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

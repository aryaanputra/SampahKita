<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  includes/fungsi.php - Helper umum (format, badge, query saldo)
 * =====================================================================
 */

if (!function_exists('e')) {
    /** Escape output untuk mencegah XSS. */
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('rupiah')) {
    /** Format angka menjadi "Rp 1.234.567". */
    function rupiah($angka, bool $prefix = true): string
    {
        $hasil = number_format((float) $angka, 0, ',', '.');
        return $prefix ? 'Rp ' . $hasil : $hasil;
    }
}

if (!function_exists('kg')) {
    /** Format berat kilogram. */
    function kg($angka): string
    {
        return number_format((float) $angka, 2, ',', '.') . ' kg';
    }
}

if (!function_exists('tanggal_id')) {
    /** Format tanggal menjadi "05 Sep 2026". */
    function tanggal_id(?string $tanggal): string
    {
        if (!$tanggal) {
            return '-';
        }
        $bulan = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
        ];
        $ts = strtotime($tanggal);
        if ($ts === false) {
            return '-';
        }
        return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}

if (!function_exists('tanggal_waktu_id')) {
    /** Format tanggal + jam menjadi "05 Sep 2026, 10:15". */
    function tanggal_waktu_id(?string $tanggal): string
    {
        if (!$tanggal) {
            return '-';
        }
        $ts = strtotime($tanggal);
        if ($ts === false) {
            return '-';
        }
        return tanggal_id($tanggal) . ', ' . date('H:i', $ts);
    }
}

if (!function_exists('get_saldo')) {
    /** Ambil saldo terkini nasabah (saldo_akhir mutasi terbaru, default 0). */
    function get_saldo(mysqli $con, int $user_id): float
    {
        $stmt = mysqli_prepare(
            $con,
            'SELECT saldo_akhir FROM saldo_mutasi WHERE user_id = ? ORDER BY id DESC LIMIT 1'
        );
        if (!$stmt) {
            return 0.0;
        }
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return $row ? (float) $row['saldo_akhir'] : 0.0;
    }
}

if (!function_exists('get_user')) {
    /** Ambil satu baris user. */
    function get_user(mysqli $con, int $id): ?array
    {
        $stmt = mysqli_prepare($con, 'SELECT * FROM users WHERE id_user = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return $row ?: null;
    }
}

if (!function_exists('badge_pencairan')) {
    /** HTML badge status pencairan. */
    function badge_pencairan(string $status): string
    {
        $map = [
            'menunggu'  => 'pending',
            'disetujui' => 'info',
            'ditolak'   => 'failed',
            'selesai'   => 'success',
        ];
        $cls = $map[$status] ?? 'pending';
        return '<span class="badge-table ' . $cls . '">' . e(ucfirst($status)) . '</span>';
    }
}

if (!function_exists('badge_jemput')) {
    /** HTML badge status penjemputan. */
    function badge_jemput(string $status): string
    {
        $map = [
            'Diminta'      => 'pending',
            'Dijadwalkan'  => 'info',
            'Selesai'      => 'success',
            'Dibatalkan'   => 'failed',
        ];
        $cls = $map[$status] ?? 'pending';
        return '<span class="badge-table ' . $cls . '">' . e($status) . '</span>';
    }
}

if (!function_exists('badge_user')) {
    /** HTML badge status akun nasabah. */
    function badge_user(array $user): string
    {
        if ((int) $user['aktif'] !== 1) {
            return '<span class="badge-table failed">Nonaktif</span>';
        }
        if (($user['status'] ?? '') !== 'verified') {
            return '<span class="badge-table pending">Belum Verifikasi</span>';
        }
        return '<span class="badge-table success">Aktif</span>';
    }
}

if (!function_exists('label_role')) {
    function label_role(string $role): string
    {
        $map = ['nasabah' => 'Nasabah', 'pengurus' => 'Pengurus', 'ketua' => 'Ketua'];
        return $map[$role] ?? ucfirst($role);
    }
}

if (!function_exists('potong_teks')) {
    function potong_teks(string $teks, int $panjang = 120): string
    {
        $teks = trim(strip_tags($teks));
        if (mb_strlen($teks) <= $panjang) {
            return $teks;
        }
        return mb_substr($teks, 0, $panjang) . '…';
    }
}

if (!function_exists('parse_estimasi_jemput')) {
    /**
     * Parse field estimasi form penjemputan menjadi jenis & estimasi berat.
     * Contoh: "Plastik, Kardus (≈ 10 kg)".
     * Mengembalikan ['jenis_ids' => array, 'berat_total' => angka].
     */
    function parse_estimasi_jemput(mysqli $con, string $estimasi): array
    {
        $jenis_ids = [];
        $berat_total = 0.0;
        $teks = trim($estimasi);
        if ($teks === '') {
            return ['jenis_ids' => $jenis_ids, 'berat_total' => $berat_total];
        }

        // Berat dihitung dari dalam kurungan, mis. "(≈ 10 kg)"; bila tidak ada
        // kurungan, cari angka yang diikuti satuan kg (mis. "Plastik 5kg").
        $bagian = $teks;
        if (preg_match('/\(([^)]*)\)/', $teks, $cocok)) {
            $bagian = trim(substr($teks, 0, strpos($teks, '(')));
            if (preg_match('/([0-9]+(?:[.,][0-9]+)?)/', $cocok[1], $bilangan)) {
                $berat_total = (float) str_replace(',', '.', $bilangan[1]);
            }
        } elseif (preg_match('/([0-9]+(?:[.,][0-9]+)?)\s*(?:kg|kilo)/i', $teks, $bilangan)) {
            $berat_total = (float) str_replace(',', '.', $bilangan[1]);
        }

        // Jenis-jenis yang dimaksud dipisah koma.
        foreach (explode(',', $bagian) as $token) {
            $token = trim($token);
            $token = trim($token, " \t\r\n(){}[]");
            if ($token === '') {
                continue;
            }
            foreach (cari_jenis_estimasi($con, $token) as $jenis_id) {
                if (!in_array($jenis_id, $jenis_ids, true)) {
                    $jenis_ids[] = $jenis_id;
                }
            }
        }

        return ['jenis_ids' => $jenis_ids, 'berat_total' => $berat_total];
    }
}

if (!function_exists('cari_jenis_estimasi')) {
    /**
     * Cari id jenis_sampah yang sesuai token (nama/kategori), [] bila tidak ada.
     */
    function cari_jenis_estimasi(mysqli $con, string $token): array
    {
        $stmt = mysqli_prepare(
            $con,
            'SELECT id, nama, kategori FROM jenis_sampah ORDER BY id'
        );
        if (!$stmt) {
            return [];
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $id = null;
        while ($r = mysqli_fetch_assoc($res)) {
            $kunci = [strtolower((string) $r['nama']), strtolower((string) $r['kategori'])];
            foreach ($kunci as $nama) {
                if ($nama !== '' && stripos($token, $nama) !== false) {
                    $id = (int) $r['id'];
                    break 2;
                }
            }
        }
        mysqli_stmt_close($stmt);
        return $id === null ? [] : [$id];
    }
}

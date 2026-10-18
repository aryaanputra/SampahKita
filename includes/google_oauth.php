<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  includes/google_oauth.php - Helper Login dengan Google (OAuth 2.0)
 * =====================================================================
 *  Implementasi alur "authorization code" memakai cURL, tanpa dependency.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../google-config.php';

if (!function_exists('google_is_configured')) {
    function google_is_configured(): bool
    {
        if (!defined('GOOGLE_CLIENT_ID') || !defined('GOOGLE_CLIENT_SECRET')) {
            return false;
        }
        return GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_ID !== 'PASTE_GOOGLE_CLIENT_ID'
            && GOOGLE_CLIENT_SECRET !== '' && GOOGLE_CLIENT_SECRET !== 'PASTE_GOOGLE_CLIENT_SECRET';
    }
}

if (!function_exists('google_redirect_uri')) {
    function google_redirect_uri(): string
    {
        if (defined('GOOGLE_REDIRECT_URI') && GOOGLE_REDIRECT_URI !== '') {
            return GOOGLE_REDIRECT_URI;
        }
        $https  = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off');
        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $dir    = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $dir    = rtrim($dir, '/');
        return $scheme . '://' . $host . $dir . '/oauth-google-callback.php';
    }
}

if (!function_exists('google_auth_url')) {
    function google_auth_url(string $state): string
    {
        $params = [
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => google_redirect_uri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ];
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
}

if (!function_exists('google_http_post')) {
    function google_http_post(string $url, array $params): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $body, 'code' => $code, 'error' => $err];
    }
}

if (!function_exists('google_http_get')) {
    function google_http_get(string $url, string $accessToken): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $body, 'code' => $code, 'error' => $err];
    }
}

if (!function_exists('google_exchange_code')) {
    /** Tukar authorization code menjadi access token. Mengembalikan string token atau null. */
    function google_exchange_code(string $code): ?string
    {
        $res = google_http_post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => google_redirect_uri(),
            'grant_type'    => 'authorization_code',
        ]);
        if ($res['body'] === false) {
            return null;
        }
        $data = json_decode((string) $res['body'], true);
        return $data['access_token'] ?? null;
    }
}

if (!function_exists('google_get_userinfo')) {
    /** Ambil profil pengguna dari Google. Mengembalikan array atau null. */
    function google_get_userinfo(string $accessToken): ?array
    {
        $res = google_http_get('https://www.googleapis.com/oauth2/v3/userinfo', $accessToken);
        if ($res['body'] === false) {
            return null;
        }
        $data = json_decode((string) $res['body'], true);
        return is_array($data) ? $data : null;
    }
}

if (!function_exists('google_login_or_register')) {
    /**
     * Cari user berdasarkan google_id / email, atau daftarkan otomatis sebagai nasabah.
     * @return array{ok:bool, user?:array, error?:string}
     */
    function google_login_or_register(mysqli $con, array $profile): array
    {
        $googleId = trim((string) ($profile['sub'] ?? ''));
        $email    = trim((string) ($profile['email'] ?? ''));
        $name     = trim((string) ($profile['name'] ?? '')) ?: $email;
        $picture  = trim((string) ($profile['picture'] ?? ''));
        $verified = $profile['email_verified'] ?? true;

        if ($googleId === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Profil Google tidak lengkap (email tidak tersedia).'];
        }
        if ($verified === false) {
            return ['ok' => false, 'error' => 'Email Google belum terverifikasi.'];
        }

        // 1) Sudah pernah login Google
        $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE google_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $googleId);
        mysqli_stmt_execute($stmt);
        $res  = mysqli_stmt_get_result($stmt);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        // 2) Cocokkan lewat email, lalu tautkan google_id
        if (!$user) {
            $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $res  = mysqli_stmt_get_result($stmt);
            $user = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);

            if ($user) {
                $stmt = mysqli_prepare($con, "UPDATE users SET google_id = ?, avatar = ? WHERE id_user = ?");
                mysqli_stmt_bind_param($stmt, "ssi", $googleId, $picture, $user['id_user']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $user['google_id'] = $googleId;
                $user['avatar']    = $picture ?: $user['avatar'];
            }
        }

        // 3) Belum ada -> daftarkan otomatis sebagai nasabah
        if (!$user) {
            $role   = 'nasabah';
            $status = 'verified';
            $stmt = mysqli_prepare(
                $con,
                "INSERT INTO users (name, email, google_id, avatar, role, status, aktif)
                 VALUES (?, ?, ?, ?, ?, ?, 1)"
            );
            mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $googleId, $picture, $role, $status);
            $ok = mysqli_stmt_execute($stmt);
            $newId = mysqli_insert_id($con);
            mysqli_stmt_close($stmt);

            if (!$ok) {
                return ['ok' => false, 'error' => 'Gagal membuat akun baru.'];
            }

            $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE id_user = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $newId);
            mysqli_stmt_execute($stmt);
            $res  = mysqli_stmt_get_result($stmt);
            $user = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);
        }

        if (!$user) {
            return ['ok' => false, 'error' => 'Terjadi kesalahan saat memuat akun.'];
        }
        if ((int) $user['aktif'] !== 1) {
            return ['ok' => false, 'error' => 'Akun Anda dinonaktifkan. Hubungi Pengurus.'];
        }

        return ['ok' => true, 'user' => $user];
    }
}

if (!function_exists('google_session_login')) {
    /** Set sesi login berdasarkan baris user, lalu arahkan ke dasbor sesuai peran. */
    function google_session_login(array $user, string $base = ''): void
    {
        session_regenerate_id(true);
        $_SESSION['id_user'] = (int) $user['id_user'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'] ?: 'nasabah';
        $_SESSION['avatar']  = $user['avatar'] ?? '';
        unset($_SESSION['google_oauth_state']);

        require_once __DIR__ . '/auth.php';
        header('Location: ' . dashboard_url($_SESSION['role'], $base));
        exit();
    }
}

<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  includes/auth.php - Session, guard peran, CSRF, flash message
 * =====================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/fungsi.php';

/* ---------------------------------------------------------------------
 * Session / identitas
 * ------------------------------------------------------------------- */
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return !empty($_SESSION['id_user']);
    }
}

if (!function_exists('current_user')) {
    function current_user(): array
    {
        return [
            'id_user' => (int) ($_SESSION['id_user'] ?? 0),
            'name'    => $_SESSION['name'] ?? '',
            'email'   => $_SESSION['email'] ?? '',
            'role'    => $_SESSION['role'] ?? '',
        ];
    }
}

if (!function_exists('app_base')) {
    /** Prefix relatif menuju root aplikasi (di-set oleh halaman via $base). */
    function app_base(): string
    {
        return $GLOBALS['base'] ?? '';
    }
}

if (!function_exists('require_login')) {
    function require_login(): void
    {
        if (!is_logged_in()) {
            $_SESSION['info'] = 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.';
            header('Location: ' . app_base() . 'login-user.php');
            exit();
        }
    }
}

if (!function_exists('require_role')) {
    /**
     * Batasi akses halaman hanya untuk peran tertentu.
     * @param array<int,string> $roles
     */
    function require_role(array $roles): void
    {
        require_login();
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, $roles, true)) {
            set_flash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            header('Location: ' . dashboard_url($role, app_base()));
            exit();
        }
    }
}

if (!function_exists('dashboard_url')) {
    function dashboard_url(string $role, string $base = ''): string
    {
        switch ($role) {
            case 'pengurus':
                return $base . 'pengurus/dashboard.php';
            case 'ketua':
                return $base . 'ketua/dashboard.php';
            default:
                return $base . 'nasabah/dashboard.php';
        }
    }
}

/* ---------------------------------------------------------------------
 * CSRF Protection
 * ------------------------------------------------------------------- */
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(): bool
    {
        return isset($_POST['csrf'], $_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
    }
}

if (!function_exists('require_csrf')) {
    function require_csrf(): void
    {
        if (!verify_csrf()) {
            http_response_code(419);
            die('Sesi form tidak valid (CSRF). Silakan muat ulang halaman dan coba kembali.');
        }
    }
}

/* ---------------------------------------------------------------------
 * Flash messages
 * ------------------------------------------------------------------- */
if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flashes')) {
    function get_flashes(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}

if (!function_exists('render_flashes')) {
    function render_flashes(): string
    {
        $html = '';
        foreach (get_flashes() as $f) {
            $type = in_array($f['type'], ['success', 'danger', 'warning', 'info'], true)
                ? $f['type']
                : 'info';
            $icon = [
                'success' => 'bi-check-circle-fill',
                'danger'  => 'bi-exclamation-octagon-fill',
                'warning' => 'bi-exclamation-triangle-fill',
                'info'    => 'bi-info-circle-fill',
            ][$type];
            $html .= '<div class="alert-custom alert-custom-' . $type . '">'
                . '<i class="bi ' . $icon . ' alert-custom-icon"></i>'
                . '<div class="alert-custom-content">' . e($f['message']) . '</div>'
                . '<button type="button" class="alert-custom-close" onclick="this.parentElement.remove()">'
                . '<i class="bi bi-x-lg"></i></button>'
                . '</div>';
        }
        return $html;
    }
}

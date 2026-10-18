<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  controllerUserData.php
 *  Menangani: signup, verifikasi OTP, login (berbasis peran),
 *  lupa password, reset password.
 * =====================================================================
 */
session_start();
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/mailer.php';

$email = "";
$name = "";
$errors = array();

// ---------------------------------------------------------------------
// Jika tombol signup ditekan
// ---------------------------------------------------------------------
if (isset($_POST['signup'])) {
    $name     = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $nik      = trim($_POST['nik'] ?? '');
    $alamat   = htmlspecialchars(trim($_POST['alamat'] ?? ''));
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    // Validasi dasar
    if ($name === '') {
        $errors['name'] = "Nama lengkap wajib diisi!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format email tidak valid!";
    }
    if (strlen($password) < 6) {
        $errors['password'] = "Password minimal 6 karakter!";
    }
    if ($password !== $cpassword) {
        $errors['password'] = "Konfirmasi password tidak cocok!";
    }
    if ($no_hp === '' || !preg_match('/^[0-9+]{8,20}$/', $no_hp)) {
        $errors['no_hp'] = "Nomor HP wajib diisi (angka, 8-20 digit)!";
    }
    if ($nik === '' || !preg_match('/^[0-9]{8,20}$/', $nik)) {
        $errors['nik'] = "NIK wajib diisi (angka, 8-20 digit)!";
    }

    // Cek keunikan email / no_hp / nik (FR-01)
    if (count($errors) === 0) {
        $stmt = mysqli_prepare($con, "SELECT id_user FROM users WHERE email = ? OR no_hp = ? OR nik = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $email, $no_hp, $nik);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $exists = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);
            if ($exists) {
                $errors['email'] = "Email, NIK, atau No. HP sudah terdaftar!";
            }
        }
    }

    if (count($errors) === 0) {
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = random_int(100000, 999999);
        $status = "notverified";

        $stmt = mysqli_prepare(
            $con,
            "INSERT INTO users (name, email, password, role, nik, no_hp, alamat, code, status, aktif)
             VALUES (?, ?, ?, 'nasabah', ?, ?, ?, ?, ?, 1)"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssis", $name, $email, $encpass, $nik, $no_hp, $alamat, $code, $status);
            $data_check = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $data_check = false;
        }

        if (!empty($data_check)) {
            $subject = "Kode Verifikasi - Bank Sampah Kali Laya Berseri";
            $message = "Halo $name,\n\nKode verifikasi pendaftaran Anda adalah $code.\n"
                     . "Masukkan kode ini pada halaman verifikasi.";
            if (sendAppEmail($email, $subject, $message)) {
                $_SESSION['info'] = "Kami telah mengirim kode verifikasi ke email - $email";
                $_SESSION['email'] = $email;
                header('location: user-otp.php');
                exit();
            } else {
                $errors['otp-error'] = "Gagal mengirim kode verifikasi. Periksa konfigurasi email.";
            }
        } else {
            $errors['db-error'] = "Gagal menyimpan data ke database!";
        }
    }
}

// ---------------------------------------------------------------------
// Verifikasi kode OTP
// ---------------------------------------------------------------------
if (isset($_POST['check'])) {
    $_SESSION['info'] = "";
    $otp_code = trim($_POST['otp'] ?? '');
    $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE code = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $otp_code);
    mysqli_stmt_execute($stmt);
    $code_res = mysqli_stmt_get_result($stmt);
    $fetch_data = $code_res ? mysqli_fetch_assoc($code_res) : null;
    mysqli_stmt_close($stmt);

    if ($fetch_data) {
        $email = $fetch_data['email'];
        $stmt = mysqli_prepare($con, "UPDATE users SET code = 0, status = 'verified' WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $fetch_data['id_user']);
        $update_res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($update_res) {
            $_SESSION['name'] = $fetch_data['name'];
            $_SESSION['email'] = $email;
            $_SESSION['info'] = "Verifikasi berhasil! Silakan masuk ke akun Anda.";
            header('location: login-user.php');
            exit();
        } else {
            $errors['otp-error'] = "Gagal memperbarui status verifikasi!";
        }
    } else {
        $errors['otp-error'] = "Kode yang Anda masukkan salah!";
    }
}

// ---------------------------------------------------------------------
// Login (berbasis peran)
// ---------------------------------------------------------------------
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare(
        $con,
        "SELECT id_user, name, email, password, status, role, aktif
         FROM users WHERE email = ? LIMIT 1"
    );

    if (!$stmt) {
        $errors['db-error'] = "Terjadi kesalahan database.";
    } else {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $fetch = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if (!$fetch || $fetch['password'] === null || !password_verify($password, $fetch['password'])) {
            $errors['email'] = "Email atau password salah. Jika akun ini dibuat lewat Google, silakan masuk dengan tombol Google.";
        } elseif ($fetch['status'] !== 'verified') {
            $_SESSION['email'] = $fetch['email'];
            $_SESSION['info'] = "Email kamu belum diverifikasi. Silakan masukkan kode OTP.";
            header('Location: user-otp.php');
            exit();
        } elseif ((int) $fetch['aktif'] !== 1) {
            $errors['email'] = "Akun Anda dinonaktifkan. Hubungi Pengurus.";
        } else {
            $role = $fetch['role'] ?: 'nasabah';
            session_regenerate_id(true);

            $_SESSION['id_user'] = $fetch['id_user'];
            $_SESSION['name']    = $fetch['name'];
            $_SESSION['email']   = $fetch['email'];
            $_SESSION['role']    = $role;

            switch ($role) {
                case 'pengurus':
                    header('Location: pengurus/dashboard.php');
                    break;
                case 'ketua':
                    header('Location: ketua/dashboard.php');
                    break;
                default:
                    header('Location: nasabah/dashboard.php');
            }
            exit();
        }
    }
}

// ---------------------------------------------------------------------
// Lupa password: kirim kode reset
// ---------------------------------------------------------------------
if (isset($_POST['check-email'])) {
    $email = trim($_POST['email'] ?? '');
    $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $run_sql = mysqli_stmt_get_result($stmt);
    $row = $run_sql ? mysqli_fetch_assoc($run_sql) : null;
    mysqli_stmt_close($stmt);

    if ($row) {
        $code = random_int(100000, 999999);
        $stmt = mysqli_prepare($con, "UPDATE users SET code = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "is", $code, $email);
        $run_query = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($run_query) {
            $subject = "Kode Reset Password - Bank Sampah Kali Laya Berseri";
            $message = "Kode reset password Anda adalah $code.";
            if (sendAppEmail($email, $subject, $message)) {
                $_SESSION['info'] = "Kami telah mengirim kode reset password ke email - $email";
                $_SESSION['email'] = $email;
                header('location: reset-code.php');
                exit();
            } else {
                $errors['otp-error'] = "Gagal mengirim kode!";
            }
        } else {
            $errors['db-error'] = "Terjadi kesalahan!";
        }
    } else {
        $errors['email'] = "Email tersebut tidak terdaftar!";
    }
}

// ---------------------------------------------------------------------
// Verifikasi kode reset
// ---------------------------------------------------------------------
if (isset($_POST['check-reset-otp'])) {
    $_SESSION['info'] = "";
    $otp_code = trim($_POST['otp'] ?? '');
    $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE code = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $otp_code);
    mysqli_stmt_execute($stmt);
    $code_res = mysqli_stmt_get_result($stmt);
    $fetch_data = $code_res ? mysqli_fetch_assoc($code_res) : null;
    mysqli_stmt_close($stmt);

    if ($fetch_data) {
        $_SESSION['email'] = $fetch_data['email'];
        $_SESSION['info'] = "Silakan buat password baru untuk akun Anda.";
        header('location: new-password.php');
        exit();
    } else {
        $errors['otp-error'] = "Kode yang Anda masukkan salah!";
    }
}

// ---------------------------------------------------------------------
// Simpan password baru
// ---------------------------------------------------------------------
if (isset($_POST['change-password'])) {
    $_SESSION['info'] = "";
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';
    if (strlen($password) < 6) {
        $errors['password'] = "Password minimal 6 karakter!";
    } elseif ($password !== $cpassword) {
        $errors['password'] = "Konfirmasi password tidak cocok!";
    } else {
        $email = $_SESSION['email'] ?? '';
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($con, "UPDATE users SET code = 0, password = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $encpass, $email);
        $run_query = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($run_query) {
            $_SESSION['info'] = "Password berhasil diubah. Silakan masuk dengan password baru.";
            header('Location: password-changed.php');
            exit();
        } else {
            $errors['db-error'] = "Gagal mengubah password!";
        }
    }
}

// ---------------------------------------------------------------------
// Tombol "Login sekarang"
// ---------------------------------------------------------------------
if (isset($_POST['login-now'])) {
    header('Location: login-user.php');
    exit();
}


<?php
require_once "controllerUserData.php";

$email = $_SESSION['email'] ?? '';

if (empty($email)) {
    header('Location: forgot-password.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verifikasi Kode - Kali Laya Berseri</title>
    <meta
        name="description"
        content="Verifikasi kode untuk mengatur ulang password akun Kali Laya Berseri."
    >

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">

    <!-- CSS Asli Spark Admin -->
    <link
        rel="stylesheet"
        href="assets/libs/bootstrap/css/bootstrap.min.css"
    >
    <link
        rel="stylesheet"
        href="assets/libs/bootstrap-icons/bootstrap-icons.css"
    >
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>
    <div class="login-wrapper">

        <!-- Background Shapes -->
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <!-- Verification Card -->
        <div class="login-card">

            <!-- Brand -->
            <a
                href="login-user.php"
                class="login-brand text-decoration-none"
            >
                <i class="bi bi-recycle"></i>
                <span>Kali Laya Berseri</span>
            </a>

            <!-- Heading -->
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i
                        class="bi bi-shield-lock"
                        style="font-size: 2.5rem;"
                        aria-hidden="true"
                    ></i>
                </div>

                <h2 class="h4 mb-2">Verifikasi Kode</h2>

                <p class="login-subtitle">
                    Kami telah mengirimkan kode verifikasi ke email Anda.
                    Masukkan kode tersebut untuk melanjutkan proses
                    pengaturan ulang password.
                </p>
            </div>

            <!-- Information Message -->
            <?php if (!empty($_SESSION['info'])): ?>
                <div class="alert alert-info" role="status">
                    <?php
                    echo htmlspecialchars(
                        $_SESSION['info'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </div>
            <?php endif; ?>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $showerror): ?>
                        <div>
                            <?php
                            echo htmlspecialchars(
                                $showerror,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- OTP Form -->
            <form
                action="reset-code.php"
                method="POST"
                id="resetCodeForm"
                autocomplete="off"
            >
                <div class="login-form-group">
                    <label
                        for="otp"
                        class="login-form-label"
                    >
                        Kode Verifikasi
                    </label>

                    <div class="login-input-group">
                        <i
                            class="bi bi-key input-icon"
                            aria-hidden="true"
                        ></i>

                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            class="login-input"
                            placeholder="Masukkan 6 digit kode"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            minlength="6"
                            maxlength="6"
                            autocomplete="one-time-code"
                            required
                            autofocus
                        >
                    </div>

                    <small class="text-muted">
                        Gunakan kode yang diterima melalui email Anda.
                    </small>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="check-reset-otp"
                    class="btn-login"
                    id="btn-submit"
                >
                    <span>Verifikasi Kode</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <!-- Footer -->
            <p class="login-footer-text mt-4">
                Ingin kembali?
                <a href="login-user.php">
                    Masuk ke akun
                </a>
            </p>

        </div>
    </div>

    <!-- Bootstrap Lokal -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Script Autentikasi Spark Admin -->
    <script src="assets/js/auth.js"></script>
</body>
</html>

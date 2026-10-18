<?php
require_once "controllerUserData.php";

// Pastikan sesi email tersedia
$email = $_SESSION['email'] ?? false;

if ($email === false || empty($email)) {
    header('Location: login-user.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Code Verification - Kali Laya Berseri</title>

    <meta name="description" content="Verify your Kali Laya Berseri account">
    <meta name="author" content="Kali Laya Berseri">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">

    <!-- Local Bootstrap 5 -->
    <link rel="stylesheet"
          href="assets/libs/bootstrap/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="assets/libs/bootstrap-icons/bootstrap-icons.css">

    <!-- Original Spark Admin CSS -->
    <link rel="stylesheet"
          href="assets/css/main.css">
</head>

<body>

    <div class="login-wrapper">

        <!-- Background Shapes -->
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <!-- Verification Card -->
        <div class="login-card">

            <!-- Brand Identity -->
            <a href="login-user.php"
               class="login-brand text-decoration-none">

                <i class="bi bi-recycle"></i>
                <span>Kali Laya Berseri</span>

            </a>

            <p class="login-subtitle">
                Verifikasi email kamu untuk melanjutkan proses pendaftaran Kali Laya Berseri.
            </p>

            <!-- Verification Form -->
            <form action="user-otp.php"
                  method="POST"
                  id="verificationForm"
                  autocomplete="off">

                <!-- Information Message -->
                <?php if (isset($_SESSION['info']) && $_SESSION['info'] !== '') { ?>

                    <div class="alert alert-success" role="status">
                        <i class="bi bi-envelope-check me-2"></i>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION['info'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </div>

                <?php } ?>

                <!-- Error Messages -->
                <?php if (count($errors) > 0) { ?>

                    <div class="alert alert-danger" role="alert">

                        <?php foreach ($errors as $showerror) { ?>

                            <p class="mb-0">
                                <?php
                                echo htmlspecialchars(
                                    $showerror,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </p>

                        <?php } ?>

                    </div>

                <?php } ?>

                <!-- Email Information -->
                <div class="login-form-group">

                    <label class="login-form-label">
                        Email Address
                    </label>

                    <div class="login-input-group">

                        <i class="bi bi-envelope input-icon"></i>

                        <input
                            type="email"
                            class="login-input"
                            value="<?php echo htmlspecialchars(
                                $email,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            readonly
                            aria-label="Registered email address">

                    </div>

                </div>

                <!-- Verification Code -->
                <div class="login-form-group">

                    <label for="otp" class="login-form-label">
                        Verification Code
                    </label>

                    <div class="login-input-group">

                        <i class="bi bi-shield-lock input-icon"></i>

                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            class="login-input"
                            placeholder="Enter verification code"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            minlength="6"
                            maxlength="6"
                            autocomplete="one-time-code"
                            required
                            autofocus>

                    </div>

                    <small class="text-muted">
                        Masukkan kode verifikasi yang dikirim ke email kamu.
                    </small>

                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="check"
                    class="btn-login"
                    id="btn-submit">

                    <span>Verify Email</span>
                    <i class="bi bi-arrow-right"></i>

                </button>

            </form>

            <!-- Footer -->
            <p class="login-footer-text mt-4">
                Salah memasukkan email?

                <a href="signup-user.php">
                    Register kembali
                </a>
            </p>

        </div>

    </div>

    <!-- Local Bootstrap -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
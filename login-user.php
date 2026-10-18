<?php
require_once "controllerUserData.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Masuk - Bank Sampah Kali Laya Berseri</title>

    <meta name="description" content="Masuk ke aplikasi Bank Sampah Kali Laya Berseri">

    <link rel="icon" type="image/png" href="assets/images/favicon.ico">

    <link rel="stylesheet"
          href="assets/libs/bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="assets/libs/bootstrap-icons/bootstrap-icons.css">

    <link rel="stylesheet"
          href="assets/css/main.css">

    <link rel="stylesheet"
          href="assets/css/app.css">
</head>

<body>

    <div class="login-wrapper">

        <!-- Background Shapes -->
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <!-- Login Card -->
        <div class="login-card">

            <!-- Brand -->
            <a href="login-user.php"
               class="login-brand text-decoration-none">

                <i class="bi bi-recycle"></i>
                <span>Kali Laya Berseri</span>

            </a>

            <p class="login-subtitle">
                Bank Sampah Kali Laya Berseri &mdash; silakan masuk untuk mengakses akun Anda.
            </p>

            <!-- Error Login Google -->
            <?php if (!empty($_SESSION['oauth_error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-google me-2"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['oauth_error'], ENT_QUOTES, 'UTF-8');
                    $_SESSION['oauth_error'] = "";
                    ?>
                </div>
            <?php } ?>

            <!-- Login Form -->
            <form action="login-user.php"
                  method="POST"
                  id="loginForm"
                  class="needs-validation"
                  autocomplete="on">

                <!-- Info Message (mis. setelah verifikasi / logout) -->
                <?php if (!empty($_SESSION['info'])) { ?>
                    <div class="alert alert-success" role="status">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php
                        echo htmlspecialchars($_SESSION['info'], ENT_QUOTES, 'UTF-8');
                        $_SESSION['info'] = "";
                        ?>
                    </div>
                <?php } ?>

                <!-- PHP Error Message -->
                <?php if (count($errors) > 0) { ?>
                    <div class="alert alert-danger text-center">
                        <?php
                        foreach ($errors as $showerror) {
                            echo htmlspecialchars(
                                $showerror,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                        }
                        ?>
                    </div>
                <?php } ?>

                <!-- Email -->
                <div class="login-form-group">

                    <label for="email" class="login-form-label">
                        Email Address
                    </label>

                    <div class="login-input-group">

                        <i class="bi bi-envelope input-icon"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="login-input"
                            placeholder="name@gmail.com"
                            autocomplete="email"
                            required
                            value="<?php echo htmlspecialchars(
                                $email ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>">

                    </div>
                </div>

                <!-- Password -->
                <div class="login-form-group">

                    <label for="password" class="login-form-label">
                        Password
                    </label>

                    <div class="login-input-group">

                        <i class="bi bi-shield-lock input-icon"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="login-input login-input-password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required>

                        <button
                            type="button"
                            class="password-toggle-btn"
                            id="toggle-password"
                            aria-label="Show password">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="login-options">

                    <label class="custom-control-label">

                        <input
                            type="checkbox"
                            class="custom-checkbox-input"
                            id="rememberMe">

                        <span>Remember Me</span>

                    </label>

                    <a href="forgot-password.php"
                       class="forgot-password-link">

                        Forgot Password?

                    </a>

                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="login"
                    class="btn-login"
                    id="btn-submit">

                    <span>Masuk</span>

                    <i class="bi bi-arrow-right"></i>

                </button>

                <!-- Divider -->
                <div class="login-divider">
                    <span>atau</span>
                </div>

                <!-- Login dengan Google -->
                <a href="oauth-google.php"
                   class="btn-google"
                   id="btn-google-login">

                    <svg class="btn-google-icon" width="20" height="20"
                         viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>

                    <span>Masuk dengan Google</span>

                </a>

            </form>

            <!-- Register -->
            <p class="login-footer-text">

                Don't have an account?

                <a href="signup-user.php" id="link-register">
                    Register Now
                </a>

            </p>

        </div>
    </div>

    <!-- Bootstrap -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Original Spark Admin Authentication Script -->
    <script src="assets/js/auth.js"></script>

</body>
</html>
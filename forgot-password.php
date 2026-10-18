<?php require_once "controllerUserData.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Kali Laya Berseri</title>

    <meta name="description"
          content="Reset your Kali Laya Berseri account password">

    <meta name="author" content="Kali Laya Berseri">

    <!-- Favicon -->
    <link rel="icon"
          type="image/png"
          href="assets/images/favicon.ico">

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

        <!-- Forgot Password Card -->
        <div class="login-card">

            <!-- Brand Identity -->
            <a href="login-user.php"
               class="login-brand text-decoration-none">

                <i class="bi bi-recycle"></i>
                <span>Kali Laya Berseri</span>

            </a>

            <!-- Page Subtitle -->
            <p class="login-subtitle">
                Forgot your password? Enter your registered email
                address to continue the password recovery process.
            </p>

            <!-- Forgot Password Form -->
            <form action="forgot-password.php"
                  method="POST"
                  id="forgotPasswordForm"
                  class="needs-validation"
                  autocomplete="on">

                <!-- PHP Error Messages -->
                <?php if (count($errors) > 0) { ?>

                    <div class="alert alert-danger text-center"
                         role="alert">

                        <?php foreach ($errors as $error) { ?>

                            <p class="mb-0">
                                <?php
                                echo htmlspecialchars(
                                    $error,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </p>

                        <?php } ?>

                    </div>

                <?php } ?>

                <!-- Email Address -->
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
                            placeholder="name@company.com"
                            autocomplete="email"
                            required
                            value="<?php echo htmlspecialchars(
                                $email ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>">

                    </div>

                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="check-email"
                    class="btn-login"
                    id="btn-submit">

                    <span>Continue</span>

                    <i class="bi bi-arrow-right"></i>

                </button>

            </form>

            <!-- Back to Login -->
            <p class="login-footer-text mt-4">

                Remember your password?

                <a href="login-user.php" id="link-login">
                    Sign In
                </a>

            </p>

        </div>

    </div>

    <!-- Local Bootstrap -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
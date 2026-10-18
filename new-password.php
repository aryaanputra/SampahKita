<?php require_once "controllerUserData.php"; ?>

<?php
$email = $_SESSION['email'] ?? false;

if ($email == false) {
    header('Location: login-user.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>New Password - Kali Laya Berseri</title>

    <meta name="description"
          content="Create a new password for your Kali Laya Berseri account">

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

        <!-- New Password Card -->
        <div class="login-card">

            <!-- Brand Identity -->
            <a href="login-user.php"
               class="login-brand text-decoration-none">

                <i class="bi bi-recycle"></i>
                <span>Kali Laya Berseri</span>

            </a>

            <!-- Page Subtitle -->
            <p class="login-subtitle">
                Create a new password to secure your account.
            </p>

            <!-- New Password Form -->
            <form action="new-password.php"
                  method="POST"
                  id="newPasswordForm"
                  class="needs-validation"
                  autocomplete="off">

                <!-- Success Message -->
                <?php if (isset($_SESSION['info'])) { ?>

                    <div class="alert alert-success text-center"
                         role="alert">

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

                    <div class="alert alert-danger text-center"
                         role="alert">

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

                <!-- New Password -->
                <div class="login-form-group">

                    <label for="password" class="login-form-label">
                        New Password
                    </label>

                    <div class="login-input-group">

                        <i class="bi bi-shield-lock input-icon"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="login-input login-input-password"
                            placeholder="Create new password"
                            autocomplete="new-password"
                            required>

                        <button
                            type="button"
                            class="password-toggle-btn"
                            data-target="password"
                            aria-label="Show password">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

                </div>

                <!-- Confirm Password -->
                <div class="login-form-group">

                    <label for="cpassword" class="login-form-label">
                        Confirm New Password
                    </label>

                    <div class="login-input-group">

                        <i class="bi bi-shield-check input-icon"></i>

                        <input
                            type="password"
                            id="cpassword"
                            name="cpassword"
                            class="login-input login-input-password"
                            placeholder="Confirm new password"
                            autocomplete="new-password"
                            required>

                        <button
                            type="button"
                            class="password-toggle-btn"
                            data-target="cpassword"
                            aria-label="Show password">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    name="change-password"
                    class="btn-login"
                    id="btn-submit">

                    <span>Change Password</span>

                    <i class="bi bi-arrow-right"></i>

                </button>

            </form>

            <!-- Footer -->
            <p class="login-footer-text mt-4">

                Want to return to login?

                <a href="login-user.php" id="link-login">
                    Sign In
                </a>

            </p>

        </div>

    </div>

    <!-- Local Bootstrap -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Password Visibility -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const toggleButtons = document.querySelectorAll(
                '.password-toggle-btn'
            );

            toggleButtons.forEach(function (button) {

                button.addEventListener('click', function () {

                    const targetId = button.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);

                    if (!passwordInput) {
                        return;
                    }

                    const isPassword =
                        passwordInput.type === 'password';

                    passwordInput.type =
                        isPassword ? 'text' : 'password';

                    button.innerHTML = isPassword
                        ? '<i class="bi bi-eye-slash"></i>'
                        : '<i class="bi bi-eye"></i>';

                    button.setAttribute(
                        'aria-label',
                        isPassword
                            ? 'Hide password'
                            : 'Show password'
                    );

                });

            });

        });
    </script>

</body>
</html>
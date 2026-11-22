<?php
session_start();
include 'config/database.php';

// 1. Handle Logout Anytime
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit;
}

// 2. Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/dashboard.php");
    exit;
}

// 3. Load Saved Credentials (with automatic default fallback)
$auth_file = __DIR__ . '/config/admin_auth.json';
$auth_data = file_exists($auth_file)
    ? json_decode(file_get_contents($auth_file), true)
    : ['username' => 'admin', 'password' => 'adminpassword', 'recovery_code' => 'samar2026'];

// Determine which form view to show: 'login', 'change', or 'forgot'
$action = $_GET['action'] ?? 'login';
$error = '';
$message = '';

// 4. Process Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? 'login';
    $username = trim($_POST['username'] ?? '');

    if ($form_type === 'login') {
        $password = trim($_POST['password'] ?? '');
        if ($username === $auth_data['username'] && $password === $auth_data['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: admin/dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } elseif ($form_type === 'change' || $form_type === 'forgot') {
        $verification = trim($_POST['verification'] ?? '');
        $new_pass = trim($_POST['new_password'] ?? '');
        $confirm_pass = trim($_POST['confirm_password'] ?? '');

        if ($username !== $auth_data['username']) {
            $error = "Admin username not found.";
        } elseif ($form_type === 'change' && $verification !== $auth_data['password']) {
            $error = "Current password is incorrect.";
        } elseif ($form_type === 'forgot' && $verification !== ($auth_data['recovery_code'] ?? 'samar2026')) {
            $error = "Invalid security recovery code.";
        } elseif (strlen($new_pass) < 4) {
            $error = "New password must be at least 4 characters long.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "New password and confirmation do not match.";
        } else {
            // Automatically creates/updates config/admin_auth.json
            $auth_data['password'] = $new_pass;
            file_put_contents($auth_file, json_encode($auth_data, JSON_PRETTY_PRINT));
            $message = "✅ Password updated! You can now log in with your new password.";
            $action = 'login';
        }
    }
}

include 'includes/header.php';
?>

<style>
    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .password-wrapper input {
        width: 100%;
        padding-right: 40px;
    }
    .toggle-password {
        position: absolute;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.1rem;
        color: #555;
        padding: 0;
        line-height: 1;
    }
    .toggle-password:hover {
        color: #1b4332;
    }
</style>

<div class="admin-form">
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div style="background: #d8f3dc; color: #1b4332; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'login'): ?>
        <!-- LOGIN FORM -->
        <h2>Admin Login</h2>
        <form method="POST">
            <input type="hidden" name="form_type" value="login">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="login_password" name="password" required>
                    <button type="button" class="toggle-password" onclick="togglePass('login_password', this)" title="Show/Hide Password">👁️</button>
                </div>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Login</button>
        </form>

        <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 12px; border-top: 1px solid #eee; font-size: 0.9em;">
            <a href="login.php?action=change" style="color: #2d6a4f; text-decoration: none; font-weight: bold;">🔑 Change Password</a>
            <a href="login.php?action=forgot" style="color: #d9534f; text-decoration: none; font-weight: bold;">❓ Forgot Password?</a>
        </div>

    <?php else: ?>
        <!-- CHANGE / FORGOT PASSWORD FORM -->
        <h2><?= $action === 'forgot' ? '❓ Reset Forgotten Password' : '🔑 Change Admin Password' ?></h2>
        <form method="POST">
            <input type="hidden" name="form_type" value="<?= htmlspecialchars($action) ?>">
            
            <div class="form-group">
                <label>Admin Username:</label>
                <input type="text" name="username" value="admin" required>
            </div>

            <div class="form-group">
                <label><?= $action === 'forgot' ? 'Security Recovery Code:' : 'Current Password:' ?></label>
                <div class="password-wrapper">
                    <input type="password" id="verification_pass" name="verification" placeholder="<?= $action === 'forgot' ? 'Default: samar2026' : 'Enter current password' ?>" required>
                    <button type="button" class="toggle-password" onclick="togglePass('verification_pass', this)" title="Show/Hide">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label>New Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="new_pass" name="new_password" required>
                    <button type="button" class="toggle-password" onclick="togglePass('new_pass', this)" title="Show/Hide">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_pass" name="confirm_password" required>
                    <button type="button" class="toggle-password" onclick="togglePass('confirm_pass', this)" title="Show/Hide">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn" style="width: 100%;">
                <?= $action === 'forgot' ? 'Reset Password' : 'Update Password' ?>
            </button>
        </form>

        <div style="margin-top: 15px; text-align: center;">
            <a href="login.php" style="color: #555; text-decoration: none;">&larr; Back to Admin Login</a>
        </div>
    <?php endif; ?>
</div>

<script>
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
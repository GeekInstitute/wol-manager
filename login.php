<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/csrf.php';

start_secure_session();

// If already logged in, redirect
if (!empty($_SESSION['user'])) {
    header("Location: " . BASE_PATH . "/pages/dashboard.php");
    exit;
}

$msg = null;

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_die($_POST['csrf'] ?? '');

    [$ok, $err] = login($_POST['username'] ?? '', $_POST['password'] ?? '');

    if ($ok) {
        header("Location: " . BASE_PATH . "/pages/dashboard.php");
        exit;
    }

    $msg = $err;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - <?= APP_NAME ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background: linear-gradient(135deg, #005bea 0%, #00c6fb 100%);
}

.login-card {
    width: 380px;
    border-radius: 15px;
    overflow: hidden;
    background: var(--bs-body-bg);
    box-shadow: 0 10px 30px rgba(0,0,0,.25);
}

.login-header {
    padding: 1.8rem;
    background: linear-gradient(135deg, #004aad 0%, #00255a 100%);
    color: white;
    text-align: center;
}

.form-control {
    padding: 10px 12px;
}
</style>
</head>

<body>

<div class="login-card">
    <div class="login-header">
        <h3><i class="bi bi-power"></i> <?= APP_NAME ?></h3>
        <p class="mb-0 small">Wake-on-LAN Web Manager</p>
    </div>

    <div class="p-4">

        <?php if ($msg): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input name="username" type="text" class="form-control" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>

            <button class="btn btn-primary w-100 py-2 fw-bold">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>



        <div class="text-center mt-3">
            <small class="text-muted">&copy; <span id="year"></span> The Geek Institute of Cyber Security</small>
        </div>
    </div>
</div>

<script>
document.getElementById("year").innerHTML = new Date().getFullYear();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

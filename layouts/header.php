<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware.php';  // session + auth
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link href="<?= BASE_PATH ?>/assets/css/app.css" rel="stylesheet">

    <script>
        /* Initial theme setup */
        (function() {
            const storedTheme = localStorage.getItem("theme");
            const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
            const theme = storedTheme || (prefersDark ? "dark" : "light");
            document.documentElement.setAttribute("data-bs-theme", theme);
        })();

        /* Theme toggle */
        function toggleTheme() {
            const current = document.documentElement.getAttribute("data-bs-theme");
            const newTheme = current === "dark" ? "light" : "dark";

            document.documentElement.setAttribute("data-bs-theme", newTheme);
            localStorage.setItem("theme", newTheme);

            // Toast notification fallback
            const alert = document.createElement("div");
            alert.className = `alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alert.innerHTML = `
                <strong>Theme:</strong> Switched to ${newTheme} mode
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);

            setTimeout(() => alert.remove(), 3000);
        }
    </script>
</head>

<body>
<script>
    const BASE_PATH = "<?= BASE_PATH ?>";
</script>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_PATH ?>/pages/dashboard.php">
            <i class="bi bi-power"></i> <?= e(APP_NAME) ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_PATH ?>/pages/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

<!-- Devices Menu -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-hdd-stack"></i> Devices
    </a>
    <ul class="dropdown-menu">

        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/devices.php">
            <i class="bi bi-list"></i> All Devices
        </a></li>

        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/servers.php">
            <i class="bi bi-server"></i> Servers
        </a></li>

        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/computers.php">
            <i class="bi bi-pc-display"></i> Computers
        </a></li>

        <?php if (is_admin()): ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/device_form.php">
                <i class="bi bi-plus-circle"></i> Add Device
            </a></li>
        <?php endif; ?>
    </ul>
</li>


<!-- Users Menu (Admin) -->
<?php if (is_admin()): ?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-people"></i> Users
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/users.php">
            <i class="bi bi-list-ul"></i> Manage Users
        </a></li>

        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/assign.php">
            <i class="bi bi-link"></i> Assign Devices
        </a></li>

        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/assign_bulk.php">
            <i class="bi bi-diagram-2"></i> Bulk Device Assignment
        </a></li>

        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/quick_assign.php">
            <i class="bi bi-lightning-charge"></i> Quick Assign Wizard
        </a></li>
  
        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/audit.php">
            <i class="bi bi-clipboard-data"></i> Audit Log
        </a></li>
        
    </ul>
</li>
<?php endif; ?>

         
            </ul>

            <!-- Right section (User + Logout + Theme) -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= e($_SESSION['user']['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/pages/profile.php">
                            <i class="bi bi-shield-lock"></i> Change Password
                        </a></li>

                        <li><a class="dropdown-item" href="#" onclick="toggleTheme()">
                            <i class="bi bi-circle-half"></i> Toggle Theme
                        </a></li>

                        <li><hr class="dropdown-divider"></li>

                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</nav>

<div class="container mt-4">

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>
<script>
    window.BASE_PATH = "<?= BASE_PATH ?>";
</script>
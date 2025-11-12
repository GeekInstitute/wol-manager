<?php
require_once __DIR__ . '/../layouts/header.php';
$uid = $_SESSION['user']['id'];

/* === Fetch Data Based on Role === */
if (is_admin()) {
    $servers = db_query("
        SELECT d.*, 1 AS can_wake, 1 AS can_shutdown, 1 AS can_console
        FROM devices d
        WHERE d.type='server'
        ORDER BY d.updated_at DESC, d.name ASC
        LIMIT 15
    ")->get_result()->fetch_all(MYSQLI_ASSOC);

    $computers = db_query("
        SELECT d.*, 1 AS can_wake, 1 AS can_shutdown, 1 AS can_console
        FROM devices d
        WHERE d.type='computer'
        ORDER BY d.updated_at DESC, d.name ASC
        LIMIT 15
    ")->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalUsers = db_query("SELECT COUNT(*) AS c FROM users")->get_result()->fetch_assoc()['c'] ?? 0;
    $blockedUsers = db_query("
        SELECT COUNT(*) AS c
        FROM users
        WHERE is_blocked_until IS NOT NULL AND is_blocked_until > NOW()
    ")->get_result()->fetch_assoc()['c'] ?? 0;

} else {
    $servers = db_query("
        SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE d.type='server' AND ud.user_id = ?
        ORDER BY d.updated_at DESC, d.name ASC
        LIMIT 15
    ", [$uid], 'i')->get_result()->fetch_all(MYSQLI_ASSOC);

    $computers = db_query("
        SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE d.type='computer' AND ud.user_id = ?
        ORDER BY d.updated_at DESC, d.name ASC
        LIMIT 15
    ", [$uid], 'i')->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalUsers = null;
    $blockedUsers = null;
}

$myServersCount   = count($servers);
$myComputersCount = count($computers);
?>

<h1 class="h3 mb-4">
    <i class="bi bi-speedometer2"></i> Dashboard
    <small class="text-muted">Welcome, <?= e($_SESSION['user']['username']) ?>!</small>
</h1>

<div class="row g-3">

    <!-- My Servers -->
    <div class="col-md-3 col-sm-6">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4><?= $myServersCount ?></h4>
                    <span>My Servers</span>
                </div>
                <i class="bi bi-server fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- My Computers -->
    <div class="col-md-3 col-sm-6">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4><?= $myComputersCount ?></h4>
                    <span>My Computers</span>
                </div>
                <i class="bi bi-pc-display fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

    <?php if (is_admin()): ?>
    <!-- Total Users -->
    <div class="col-md-3 col-sm-6">
        <div class="card bg-warning text-dark shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4><?= $totalUsers ?></h4>
                    <span>Total Users</span>
                </div>
                <i class="bi bi-people fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Blocked Users -->
    <div class="col-md-3 col-sm-6">
        <div class="card bg-danger text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4><?= $blockedUsers ?></h4>
                    <span>Blocked Users</span>
                </div>
                <i class="bi bi-person-x fs-1 opacity-75"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Online Devices -->
    <div class="col-md-3 col-sm-6">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 id="onlineDevicesQuick">—</h4>
                    <span>Online Devices</span>
                </div>
                <i class="bi bi-wifi fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Online Servers -->
    <div class="col-md-3 col-sm-6">
        <div class="card bg-secondary text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 id="onlineServersQuick">—</h4>
                    <span>Online Servers</span>
                </div>
                <i class="bi bi-hdd-network fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Online Computers -->
    <div class="col-md-3 col-sm-6">
        <div class="card bg-dark text-white shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 id="onlineComputersQuick">—</h4>
                    <span>Online Computers</span>
                </div>
                <i class="bi bi-laptop fs-1 opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<!-- =================== RECENT SERVERS =================== -->
<div class="row">
    <?php if (!empty($servers)): ?>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-server"></i> Recent Servers (15)</strong>
                <a href="<?= BASE_PATH ?>/pages/servers.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>OS</th>
                        <th>IP</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($servers as $s): ?>
                    <tr>
                        <td><?= e($s['name']) ?></td>
                        <td><?= $s['os'] === 'linux'
                            ? '<span class="badge bg-dark">Linux</span>'
                            : '<span class="badge bg-info text-dark">Windows</span>' ?></td>
                        <td><?= e($s['ip']) ?></td>
                        <td>
                            <span data-device="<?= $s['id'] ?>"><span class="badge bg-secondary">...</span></span>
                            <span data-ssh="<?= $s['id'] ?>" class="ms-1"><span class="badge bg-secondary">...</span></span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <?php if ($s['can_wake']): ?>
                                    <a data-wake="<?= $s['id'] ?>" href="<?= BASE_PATH ?>/actions/device_wake.php?id=<?= $s['id'] ?>&csrf=<?= csrf_token() ?>"
                                       class="btn btn-outline-success" title="Wake"><i class="bi bi-arrow-up-circle"></i></a>
                                <?php endif; ?>
                                <?php if ($s['can_shutdown']): ?>
                                    <a data-shutdown="<?= $s['id'] ?>" href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $s['id'] ?>&csrf=<?= csrf_token() ?>"
                                       class="btn btn-outline-danger" title="Shutdown"><i class="bi bi-power"></i></a>
                                <?php endif; ?>
                                <?php if ($s['can_console']): ?>
                                    <a data-console="<?= $s['id'] ?>" href="<?= BASE_PATH ?>/pages/console.php?device=<?= $s['id'] ?>"
                                       class="btn btn-outline-secondary disabled" title="Console (online only)" disabled>
                                       <i class="bi bi-terminal"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- =================== RECENT COMPUTERS =================== -->
    <?php if (!empty($computers)): ?>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-pc-display"></i> Recent Computers (15)</strong>
                <a href="<?= BASE_PATH ?>/pages/computers.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>OS</th>
                        <th>IP</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($computers as $c): ?>
                    <tr>
                        <td><?= e($c['name']) ?></td>
                        <td><?= $c['os'] === 'linux'
                            ? '<span class="badge bg-dark">Linux</span>'
                            : '<span class="badge bg-info text-dark">Windows</span>' ?></td>
                        <td><?= e($c['ip']) ?></td>
                        <td>
                            <span data-device="<?= $c['id'] ?>"><span class="badge bg-secondary">...</span></span>
                            <span data-ssh="<?= $c['id'] ?>" class="ms-1"><span class="badge bg-secondary">...</span></span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <?php if ($c['can_wake']): ?>
                                    <a data-wake="<?= $c['id'] ?>" href="<?= BASE_PATH ?>/actions/device_wake.php?id=<?= $c['id'] ?>&csrf=<?= csrf_token() ?>"
                                       class="btn btn-outline-success" title="Wake"><i class="bi bi-arrow-up-circle"></i></a>
                                <?php endif; ?>
                                <?php if ($c['can_shutdown']): ?>
                                    <a data-shutdown="<?= $c['id'] ?>" href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $c['id'] ?>&csrf=<?= csrf_token() ?>"
                                       class="btn btn-outline-danger" title="Shutdown"><i class="bi bi-power"></i></a>
                                <?php endif; ?>
                                <?php if ($c['can_console']): ?>
                                    <a data-console="<?= $c['id'] ?>" href="<?= BASE_PATH ?>/pages/console.php?device=<?= $c['id'] ?>"
                                       class="btn btn-outline-secondary disabled" title="Console (online only)" disabled>
                                       <i class="bi bi-terminal"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Live Status -->
<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>
<script>
async function updateQuickStats() {
    try {
        const res = await fetch("<?= BASE_PATH ?>/api/status.php");
        const data = await res.json();

        const servers = data.filter(d => d.type === "server");
        const computers = data.filter(d => d.type === "computer");

        document.getElementById("onlineDevicesQuick").textContent = data.filter(x => x.online).length;
        document.getElementById("onlineServersQuick").textContent = servers.filter(x => x.online).length;
        document.getElementById("onlineComputersQuick").textContent = computers.filter(x => x.online).length;
    } catch (e) {}
}

setInterval(updateQuickStats, 4000);
document.addEventListener("DOMContentLoaded", updateQuickStats);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$uid = $_SESSION['user']['id'];

/*  Get devices based on user role & permissions */
if (is_admin()) {
    $servers = db_query("SELECT d.*, 1 AS can_wake, 1 AS can_shutdown, 1 AS can_console
                         FROM devices d WHERE d.type='server'
                         ORDER BY d.updated_at DESC, d.name ASC")->get_result()->fetch_all(MYSQLI_ASSOC);

    $computers = db_query("SELECT d.*, 1 AS can_wake, 1 AS can_shutdown, 1 AS can_console
                           FROM devices d WHERE d.type='computer'
                           ORDER BY d.updated_at DESC, d.name ASC")->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalUsers = db_query("SELECT COUNT(*) c FROM users")->get_result()->fetch_assoc()['c'] ?? 0;

} else {
    $servers = db_query("SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
                         FROM devices d
                         JOIN user_devices ud ON ud.device_id = d.id
                         WHERE d.type='server' AND ud.user_id = ?
                         ORDER BY d.updated_at DESC, d.name ASC", [$uid], 'i')
               ->get_result()->fetch_all(MYSQLI_ASSOC);

    $computers = db_query("SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
                           FROM devices d
                           JOIN user_devices ud ON ud.device_id = d.id
                           WHERE d.type='computer' AND ud.user_id = ?
                           ORDER BY d.updated_at DESC, d.name ASC", [$uid], 'i')
                 ->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalUsers = null;
}

$myServersCount   = count($servers);
$myComputersCount = count($computers);
?>

<h1 class="h3 mb-4">
    <i class="bi bi-speedometer2"></i> Dashboard
    <small class="text-muted">Welcome, <?= e($_SESSION['user']['username']) ?>!</small>
</h1>

<div class="row">

    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <h4><?= $myServersCount ?></h4>
                    <span>My Servers</span>
                </div>
                <i class="bi bi-server fs-1"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <h4><?= $myComputersCount ?></h4>
                    <span>My Computers</span>
                </div>
                <i class="bi bi-pc-display fs-1"></i>
            </div>
        </div>
    </div>

    <?php if (is_admin()): ?>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <h4><?= $totalUsers ?></h4>
                    <span>Total Users</span>
                </div>
                <i class="bi bi-people fs-1"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <h4 id="onlineDevicesQuick">—</h4>
                    <span>Online Devices</span>
                </div>
                <i class="bi bi-wifi fs-1"></i>
            </div>
        </div>
    </div>

</div>



<div class="row">
    <!-- =================== SERVERS =================== -->
    <?php if (!empty($servers)): ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong><i class="bi bi-server"></i> My Recent Servers</strong>
                <a href="<?= BASE_PATH ?>/pages/servers.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>OS</th>
                        <th>IP</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_slice($servers, 0, 5) as $s): ?>
                    <tr>
                        <td><?= e($s['name']) ?></td>
                        <td><?= $s['os'] === 'linux' ? '<span class="badge bg-dark">Linux</span>' : '<span class="badge bg-info text-dark">Windows</span>' ?></td>
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

                                <?php if (is_admin()): ?>
                                <a href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $s['id'] ?>"
                                   class="btn btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
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



    <!-- =================== COMPUTERS =================== -->
    <?php if (!empty($computers)): ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong><i class="bi bi-pc-display"></i> My Recent Computers</strong>
                <a href="<?= BASE_PATH ?>/pages/computers.php" class="btn btn-sm btn-primary">View All</a>
            </div>

            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>OS</th>
                        <th>IP</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_slice($computers, 0, 5) as $c): ?>
                    <tr>
                        <td><?= e($c['name']) ?></td>
                        <td><?= $c['os'] === 'linux' ? '<span class="badge bg-dark">Linux</span>' : '<span class="badge bg-info text-dark">Windows</span>' ?></td>
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
                                   class="btn btn-outline-secondary disabled" disabled title="Console (online only)">
                                   <i class="bi bi-terminal"></i>
                                </a>
                                <?php endif; ?>

                                <?php if (is_admin()): ?>
                                <a href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $c['id'] ?>"
                                   class="btn btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
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
<!-- Status Script -->
<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>
<script>
async function updateOnlineQuickBox() {
    try {
        const res = await fetch("<?= BASE_PATH ?>/api/status.php");
        const data = await res.json();
        document.getElementById("onlineDevicesQuick").textContent = data.filter(x => x.online).length;
    } catch (e) {}
}

setInterval(updateOnlineQuickBox, 4000);
document.addEventListener("DOMContentLoaded", updateOnlineQuickBox);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

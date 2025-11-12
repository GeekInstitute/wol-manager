<?php
require_once __DIR__ . '/../layouts/header.php';

$uid = $_SESSION['user']['id'];

/* Fetch data */
if (is_admin()) {
    $servers = db_query("SELECT * FROM devices WHERE type='server' ORDER BY updated_at DESC, name ASC")->get_result()->fetch_all(MYSQLI_ASSOC);
    $computers = db_query("SELECT * FROM devices WHERE type='computer' ORDER BY updated_at DESC, name ASC")->get_result()->fetch_all(MYSQLI_ASSOC);
    $totalUsers = db_query("SELECT COUNT(*) c FROM users")->get_result()->fetch_assoc()['c'] ?? 0;
    $blockedUsers = db_query("SELECT COUNT(*) c FROM users WHERE is_blocked_until IS NOT NULL AND is_blocked_until > NOW()")->get_result()->fetch_assoc()['c'] ?? 0;
} else {
    $servers = db_query("
        SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE d.type='server' AND ud.user_id = ?
        ORDER BY d.updated_at DESC, d.name ASC
    ", [$uid], 'i')->get_result()->fetch_all(MYSQLI_ASSOC);

    $computers = db_query("
        SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE d.type='computer' AND ud.user_id = ?
        ORDER BY d.updated_at DESC, d.name ASC
    ", [$uid], 'i')->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalUsers = null;
    $blockedUsers = null;
}

$myServersCount   = count($servers);
$myComputersCount = count($computers);
$totalDevicesCount = $myServersCount + $myComputersCount;
?>

<h1 class="h3 mb-4">
  <i class="bi bi-speedometer2"></i> Dashboard
  <small class="text-muted">Welcome, <?= e($_SESSION['user']['username']) ?>!</small>
</h1>

<!-- ====== CARDS SECTION ====== -->
<div class="row g-3">

  <!-- Total Devices -->
  <div class="col-md-3">
    <div class="card bg-dark text-white shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4><?= $totalDevicesCount ?></h4>
          <span>Total Devices</span>
        </div>
        <i class="bi bi-hdd-stack fs-1"></i>
      </div>
    </div>
  </div>

  <!-- Servers -->
  <div class="col-md-3">
    <div class="card bg-primary text-white shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4><?= $myServersCount ?></h4>
          <span>My Servers</span>
        </div>
        <i class="bi bi-server fs-1"></i>
      </div>
    </div>
  </div>

  <!-- Computers -->
  <div class="col-md-3">
    <div class="card bg-success text-white shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4><?= $myComputersCount ?></h4>
          <span>My Computers</span>
        </div>
        <i class="bi bi-pc-display fs-1"></i>
      </div>
    </div>
  </div>

  <!-- Admin Only -->
  <?php if (is_admin()): ?>
  <div class="col-md-3">
    <div class="card bg-warning text-dark shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4><?= $totalUsers ?></h4>
          <span>Total Users</span>
        </div>
        <i class="bi bi-people fs-1"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card bg-danger text-white shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4><?= $blockedUsers ?></h4>
          <span>Blocked Users</span>
        </div>
        <i class="bi bi-person-x fs-1"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Online Device Stats -->
  <div class="col-md-3">
    <div class="card bg-info text-white shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 id="onlineDevicesQuick">—</h4>
          <span>Online Devices</span>
        </div>
        <i class="bi bi-wifi fs-1"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card bg-secondary text-white shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 id="onlineServersQuick">—</h4>
          <span>Online Servers</span>
        </div>
        <i class="bi bi-server fs-1"></i>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card bg-secondary text-white shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h4 id="onlineComputersQuick">—</h4>
          <span>Online Computers</span>
        </div>
        <i class="bi bi-pc-display fs-1"></i>
      </div>
    </div>
  </div>
</div>

<hr class="my-4">

<!-- ====== RECENT SERVERS ====== -->
<div class="row g-4">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-server"></i> Recent Servers</strong>
        <a href="<?= BASE_PATH ?>/pages/servers.php" class="btn btn-sm btn-primary">View All</a>
      </div>

      <table class="table table-sm table-striped align-middle mb-0">
        <thead>
          <tr><th>Name</th><th>OS</th><th>IP</th><th>Status</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($servers, 0, 15) as $s): ?>
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

                <?php if (is_admin() || !empty($s['can_wake'])): ?>
                  <a data-wake="<?= $s['id'] ?>" href="<?= BASE_PATH ?>/actions/device_wake.php?id=<?= $s['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-outline-success disabled" title="Wake">
                    <i class="bi bi-arrow-up-circle"></i>
                  </a>
                <?php endif; ?>

                <?php if (is_admin() || !empty($s['can_shutdown'])): ?>
                  <a data-shutdown="<?= $s['id'] ?>" href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $s['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-outline-danger disabled" title="Shutdown">
                    <i class="bi bi-power"></i>
                  </a>
                <?php endif; ?>

                <?php if (is_admin() || !empty($s['can_console'])): ?>
                  <a data-console="<?= $s['id'] ?>" href="<?= BASE_PATH ?>/pages/console.php?device=<?= $s['id'] ?>" class="btn btn-outline-secondary disabled" title="Console">
                    <i class="bi bi-terminal"></i>
                  </a>
                <?php endif; ?>

                <?php if (is_admin()): ?>
                  <a href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $s['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                  <a href="<?= BASE_PATH ?>/actions/device_delete.php?id=<?= $s['id'] ?>&csrf=<?= csrf_token() ?>" onclick="return confirm('Delete this device?')" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ====== RECENT COMPUTERS ====== -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-pc-display"></i> Recent Computers</strong>
        <a href="<?= BASE_PATH ?>/pages/computers.php" class="btn btn-sm btn-primary">View All</a>
      </div>

      <table class="table table-sm table-striped align-middle mb-0">
        <thead>
          <tr><th>Name</th><th>OS</th><th>IP</th><th>Status</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($computers, 0, 15) as $c): ?>
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
                <?php if (is_admin() || !empty($c['can_wake'])): ?>
                  <a data-wake="<?= $c['id'] ?>" href="<?= BASE_PATH ?>/actions/device_wake.php?id=<?= $c['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-outline-success disabled" title="Wake"><i class="bi bi-arrow-up-circle"></i></a>
                <?php endif; ?>
                <?php if (is_admin() || !empty($c['can_shutdown'])): ?>
                  <a data-shutdown="<?= $c['id'] ?>" href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $c['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-outline-danger disabled" title="Shutdown"><i class="bi bi-power"></i></a>
                <?php endif; ?>
                <?php if (is_admin() || !empty($c['can_console'])): ?>
                  <a data-console="<?= $c['id'] ?>" href="<?= BASE_PATH ?>/pages/console.php?device=<?= $c['id'] ?>" class="btn btn-outline-secondary disabled" title="Console"><i class="bi bi-terminal"></i></a>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                  <a href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $c['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                  <a href="<?= BASE_PATH ?>/actions/device_delete.php?id=<?= $c['id'] ?>&csrf=<?= csrf_token() ?>" onclick="return confirm('Delete this device?')" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ====== JS ====== -->
<script>const BASE_PATH = "<?= BASE_PATH ?>";</script>
<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>

<script>
async function updateQuickStats() {
  try {
    const res = await fetch(`${BASE_PATH}/api/status.php?ts=${Date.now()}`, { cache: "no-store" });
    const data = await res.json();

    const onlineDevices = data.filter(d => d.online).length;
    const onlineServers = data.filter(d => d.type === "server" && d.online).length;
    const onlineComputers = data.filter(d => d.type === "computer" && d.online).length;

    document.getElementById("onlineDevicesQuick").textContent = onlineDevices;
    document.getElementById("onlineServersQuick").textContent = onlineServers;
    document.getElementById("onlineComputersQuick").textContent = onlineComputers;
  } catch (err) {
    console.error("Failed to update dashboard stats:", err);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  updateQuickStats();
  refreshStatus();
  setInterval(updateQuickStats, 5000);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

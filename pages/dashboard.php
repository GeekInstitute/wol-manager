<?php
require_once __DIR__ . '/../layouts/header.php';

$uid = $_SESSION['user']['id'];

/* Fetch devices and user counts */
if (is_admin()) {
    $servers = db_query("SELECT * FROM devices WHERE type='server' ORDER BY updated_at DESC, name ASC")->get_result()->fetch_all(MYSQLI_ASSOC);
    $computers = db_query("SELECT * FROM devices WHERE type='computer' ORDER BY updated_at DESC, name ASC")->get_result()->fetch_all(MYSQLI_ASSOC);
    $totalUsers = db_query("SELECT COUNT(*) c FROM users")->get_result()->fetch_assoc()['c'] ?? 0;
    $blockedUsers = db_query("SELECT COUNT(*) c FROM users WHERE is_blocked_until IS NOT NULL AND is_blocked_until > NOW()")->get_result()->fetch_assoc()['c'] ?? 0;
} else {
    $servers = db_query(
        "SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
         FROM devices d
         JOIN user_devices ud ON ud.device_id = d.id
         WHERE d.type='server' AND ud.user_id = ?
         ORDER BY d.updated_at DESC, d.name ASC",
        [$uid],
        'i'
    )->get_result()->fetch_all(MYSQLI_ASSOC);

    $computers = db_query(
        "SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
         FROM devices d
         JOIN user_devices ud ON ud.device_id = d.id
         WHERE d.type='computer' AND ud.user_id = ?
         ORDER BY d.updated_at DESC, d.name ASC",
        [$uid],
        'i'
    )->get_result()->fetch_all(MYSQLI_ASSOC);

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

<div class="row g-3">
  <!-- Total Devices -->
  <div class="col-md-3">
    <div class="card bg-dark text-white shadow-sm">
      <div class="card-body d-flex justify-content-between">
        <div>
          <h4><?= $totalDevicesCount ?></h4>
          <span>Total Devices</span>
        </div>
        <i class="bi bi-hdd-stack fs-1"></i>
      </div>
    </div>
  </div>

  <!-- My Servers -->
  <div class="col-md-3">
    <div class="card bg-primary text-white shadow-sm">
      <div class="card-body d-flex justify-content-between">
        <div>
          <h4><?= $myServersCount ?></h4>
          <span>My Servers</span>
        </div>
        <i class="bi bi-server fs-1"></i>
      </div>
    </div>
  </div>

  <!-- My Computers -->
  <div class="col-md-3">
    <div class="card bg-success text-white shadow-sm">
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
  <!-- Total Users -->
  <div class="col-md-3">
    <div class="card bg-warning text-dark shadow-sm">
      <div class="card-body d-flex justify-content-between">
        <div>
          <h4><?= $totalUsers ?></h4>
          <span>Total Users</span>
        </div>
        <i class="bi bi-people fs-1"></i>
      </div>
    </div>
  </div>

  <!-- Blocked Users -->
  <div class="col-md-3">
    <div class="card bg-danger text-white shadow-sm">
      <div class="card-body d-flex justify-content-between">
        <div>
          <h4><?= $blockedUsers ?></h4>
          <span>Blocked Users</span>
        </div>
        <i class="bi bi-person-x fs-1"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Online Stats -->
  <div class="col-md-3">
    <div class="card bg-info text-white shadow-sm">
      <div class="card-body d-flex justify-content-between">
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
      <div class="card-body d-flex justify-content-between">
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
      <div class="card-body d-flex justify-content-between">
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

<!-- =================== Recent Servers & Computers =================== -->
<div class="row g-4">
  <!-- Recent Servers -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-server"></i> Recent Servers</strong>
        <a href="<?= BASE_PATH ?>/pages/servers.php" class="btn btn-sm btn-primary">View All</a>
      </div>
      <table class="table table-sm table-striped align-middle mb-0">
        <thead><tr><th>Name</th><th>OS</th><th>IP</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($servers, 0, 15) as $s): ?>
          <tr>
            <td><?= e($s['name']) ?></td>
            <td><?= $s['os'] === 'linux' ? 'Linux' : 'Windows' ?></td>
            <td><?= e($s['ip']) ?></td>
            <td><span data-device="<?= $s['id'] ?>"><span class="badge bg-secondary">...</span></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Computers -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-pc-display"></i> Recent Computers</strong>
        <a href="<?= BASE_PATH ?>/pages/computers.php" class="btn btn-sm btn-primary">View All</a>
      </div>
      <table class="table table-sm table-striped align-middle mb-0">
        <thead><tr><th>Name</th><th>OS</th><th>IP</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($computers, 0, 15) as $c): ?>
          <tr>
            <td><?= e($c['name']) ?></td>
            <td><?= $c['os'] === 'linux' ? 'Linux' : 'Windows' ?></td>
            <td><?= e($c['ip']) ?></td>
            <td><span data-device="<?= $c['id'] ?>"><span class="badge bg-secondary">...</span></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>
<script>
async function updateQuickStats() {
  try {
    const res = await fetch("<?= BASE_PATH ?>/api/status.php?ts=" + Date.now());
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

setInterval(updateQuickStats, 8000);
document.addEventListener("DOMContentLoaded", updateQuickStats);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

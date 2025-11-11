<?php
require_once __DIR__ . '/../middleware.php';
require_login();

include "../layouts/header.php";

$uid = $_SESSION['user']['id'];

/* Fetch computers based on permissions */
if (is_admin()) {
    $result = db_query("
        SELECT * FROM devices 
        WHERE type='computer'
        ORDER BY name ASC
    ")->get_result();
} else {
    $result = db_query("
        SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE d.type='computer' AND ud.user_id = ?
        ORDER BY d.name ASC
    ", [$uid], "i")->get_result();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-pc-display"></i> Computers</h3>

    <!-- Wake All / Shutdown All -->
    <div class="btn-group btn-group-sm">

        <?php if (is_admin()): ?>
            <a data-wake-all href="<?= BASE_PATH ?>/actions/device_wake_all.php?type=computer&csrf=<?= csrf_token() ?>"
               class="btn btn-success disabled"
               onclick="return confirm('Wake ALL OFFLINE computers?')">
               <i class="bi bi-arrow-up-circle"></i> Wake All Devices
            </a>

            <a data-shutdown-all href="<?= BASE_PATH ?>/actions/device_shutdown_all.php?type=computer&csrf=<?= csrf_token() ?>"
               class="btn btn-danger disabled"
               onclick="return confirm('Shutdown ALL ONLINE computers?')">
               <i class="bi bi-power"></i> Shutdown All Devices
            </a>
        <?php else: ?>
            <a data-wake-all href="<?= BASE_PATH ?>/actions/device_wake_all.php?type=computer&csrf=<?= csrf_token() ?>"
               class="btn btn-success disabled">
               <i class="bi bi-arrow-up-circle"></i> Wake My All Devices
            </a>

            <a data-shutdown-all href="<?= BASE_PATH ?>/actions/device_shutdown_all.php?type=computer&csrf=<?= csrf_token() ?>"
               class="btn btn-danger disabled">
               <i class="bi bi-power"></i> Shutdown My All Devices
            </a>
        <?php endif; ?>

    </div>
</div>

<input type="text" id="deviceSearch" class="form-control mb-2" placeholder="Search computers (name / IP)">

<div class="table-responsive">
<table class="table table-hover table-striped" id="deviceTable">
<thead class="table-dark">
<tr>
    <th>Name</th>
    <th>OS</th>
    <th>IP</th>
    <th>Status</th>
    <th class="text-end">Actions</th>
</tr>
</thead>
<tbody>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= e($row['name']) ?></td>

    <td>
        <?php if ($row['os'] === 'linux'): ?>
            <span class="badge bg-dark">Linux</span>
        <?php else: ?>
            <span class="badge bg-info text-dark">Windows</span>
        <?php endif; ?>
    </td>

    <td><?= e($row['ip']) ?></td>

    <td>
        <span data-device="<?= $row['id'] ?>"><span class="badge bg-secondary">...</span></span>
        <span data-ssh="<?= $row['id'] ?>" class="ms-1"><span class="badge bg-secondary">...</span></span>
    </td>

    <td class="text-end">
        <div class="btn-group btn-group-sm">

            <!-- Wake -->
            <?php if (is_admin() || !empty($row['can_wake'])): ?>
                <a data-wake="<?= $row['id'] ?>"
                   href="<?= BASE_PATH ?>/actions/device_wake.php?id=<?= $row['id'] ?>&csrf=<?= csrf_token() ?>"
                   class="btn btn-outline-success disabled"
                   title="Wake (enabled when offline)">
                   <i class="bi bi-arrow-up-circle"></i>
                </a>
            <?php endif; ?>

            <!-- Shutdown -->
            <?php if (is_admin() || !empty($row['can_shutdown'])): ?>
                <a data-shutdown="<?= $row['id'] ?>"
                   href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $row['id'] ?>&csrf=<?= csrf_token() ?>"
                   class="btn btn-outline-danger disabled"
                   title="Shutdown (enabled when online)">
                   <i class="bi bi-power"></i>
                </a>
            <?php endif; ?>

            <!-- Console -->
            <?php if (is_admin() || !empty($row['can_console'])): ?>
                <a data-console="<?= $row['id'] ?>"
                   href="<?= BASE_PATH ?>/pages/console.php?device=<?= $row['id'] ?>"
                   class="btn btn-outline-secondary disabled"
                   title="Console (Enabled when online)">
                   <i class="bi bi-terminal"></i>
                </a>
            <?php endif; ?>

            <!-- Admin only: Edit/Delete -->
            <?php if (is_admin()): ?>
                <a href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $row['id'] ?>"
                   class="btn btn-outline-primary" title="Edit">
                   <i class="bi bi-pencil-square"></i>
                </a>

                <a href="<?= BASE_PATH ?>/actions/device_delete.php?id=<?= $row['id'] ?>&csrf=<?= csrf_token() ?>"
                   class="btn btn-outline-danger"
                   onclick="return confirm('Delete this device?')"
                   title="Delete">
                   <i class="bi bi-trash"></i>
                </a>
            <?php endif; ?>

        </div>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>

<script>
document.getElementById("deviceSearch").addEventListener("keyup", function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll("#deviceTable tbody tr").forEach(tr =>
        tr.style.display = tr.innerText.toLowerCase().includes(q) ? "" : "none"
    );
});
</script>

<?php include "../layouts/footer.php"; ?>

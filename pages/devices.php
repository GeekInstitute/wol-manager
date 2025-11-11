<?php
require_once __DIR__ . '/../middleware.php';
require_login();

include "../layouts/header.php";
?>

<div class="d-flex align-items-center mb-3">
    <h3 class="me-auto"><i class="bi bi-hdd-stack"></i> Devices</h3>

    <?php if (is_admin()): ?>
        <a class="btn btn-primary" href="<?= BASE_PATH ?>/pages/device_form.php">
            <i class="bi bi-plus-circle"></i> Add Device
        </a>
    <?php endif; ?>
</div>

<!-- Search -->
<input type="text" id="searchDevice" class="form-control mb-3"
       placeholder="Search devices by name / IP / MAC / OS / Type...">

<?php
$res = db_query("SELECT * FROM devices ORDER BY type DESC, name ASC")->get_result();
?>

<div class="table-responsive">
<table class="table table-hover align-middle" id="devicesTable">
    <thead class="table-dark">
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>OS</th>
            <th>IP</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>

    <tbody>
<?php while ($d = $res->fetch_assoc()): ?>
    <tr>
        <td><?= e($d['name']) ?></td>
        <td><?= ucfirst(e($d['type'])) ?></td>
        <td><?= ucfirst(e($d['os'])) ?></td>
        <td><?= e($d['ip']) ?></td>

        <!--  Live device + ssh status -->
        <td>
            <span data-device="<?= $d['id'] ?>"><span class="badge bg-secondary">...</span></span>
            <span data-ssh="<?= $d['id'] ?>" class="ms-1"><span class="badge bg-secondary">...</span></span>
        </td>

        <td class="text-end">
            <div class="btn-group btn-group-sm">

                <!--  Wake -->
                <a data-wake="<?= $d['id'] ?>"
                    href="<?= BASE_PATH ?>/actions/device_wake.php?id=<?= $d['id'] ?>&csrf=<?= csrf_token() ?>"
                    class="btn btn-outline-success" title="Wake">
                    <i class="bi bi-arrow-up-circle"></i>
                </a>

                <!--  Shutdown -->
                <a data-shutdown="<?= $d['id'] ?>"
                    href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $d['id'] ?>&csrf=<?= csrf_token() ?>"
                    class="btn btn-outline-danger" title="Shutdown">
                    <i class="bi bi-power"></i>
                </a>

                <!--  Console -->
                <a data-console="<?= $d['id'] ?>"
                href="<?= BASE_PATH ?>/pages/console.php?device=<?= $d['id'] ?>"
                class="btn btn-outline-secondary disabled"
                title="Console (Online only)"
                aria-disabled="true">
                    <i class="bi bi-terminal"></i>
                </a>

                <?php if (is_admin()): ?>
                    <!-- Edit -->
                    <a class="btn btn-outline-primary"
                        href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $d['id'] ?>"
                        title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>

                    <!-- Delete -->
                    <a class="btn btn-outline-danger"
                        href="<?= BASE_PATH ?>/actions/device_delete.php?id=<?= $d['id'] ?>&csrf=<?= csrf_token() ?>"
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
<!-- Status Script -->
<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>
<script>
document.getElementById("searchDevice").addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#devicesTable tbody tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
});
</script>

<?php include "../layouts/footer.php"; ?>

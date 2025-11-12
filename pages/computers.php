<?php
require_once __DIR__ . '/../middleware.php';
require_login();
include "../layouts/header.php";

$uid = $_SESSION['user']['id'];

/* Pagination setup */
$limit = 10; // Devices per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* Count and fetch devices */
if (is_admin()) {
    $countResult = db_query("SELECT COUNT(*) AS total FROM devices WHERE type='computer'")->get_result()->fetch_assoc();
    $total = $countResult['total'];

    $query = "SELECT * FROM devices WHERE type='computer' ORDER BY name ASC LIMIT ?, ?";
    $params = [$offset, $limit];
    $types = "ii";
} else {
    $countResult = db_query("
        SELECT COUNT(*) AS total
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE d.type='computer' AND ud.user_id = ?", [$uid], "i"
    )->get_result()->fetch_assoc();
    $total = $countResult['total'];

    $query = "
        SELECT d.*, ud.can_wake, ud.can_shutdown, ud.can_console
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE d.type='computer' AND ud.user_id = ?
        ORDER BY d.name ASC
        LIMIT ?, ?";
    $params = [$uid, $offset, $limit];
    $types = "iii";
}

$result = db_query($query, $params, $types)->get_result();
$totalPages = ceil($total / $limit);
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h3 class="me-auto"><i class="bi bi-pc-display"></i> Computers</h3>

    <div class="btn-group btn-group-sm flex-wrap">
        <!-- Wake All / Shutdown All -->
        <a data-wake-all href="<?= BASE_PATH ?>/actions/device_wake_all.php?type=computer&csrf=<?= csrf_token() ?>"
           class="btn btn-success disabled"
           onclick="return confirm('Wake ALL offline computers?')">
           <i class="bi bi-arrow-up-circle"></i> Wake All
        </a>

        <a data-shutdown-all href="<?= BASE_PATH ?>/actions/device_shutdown_all.php?type=computer&csrf=<?= csrf_token() ?>"
           class="btn btn-danger disabled"
           onclick="return confirm('Shutdown ALL online computers?')">
           <i class="bi bi-power"></i> Shutdown All
        </a>

        <!-- Wake / Shutdown Selected -->
        <button id="wakeSelectedBtn" class="btn btn-outline-success disabled">
            <i class="bi bi-arrow-up-circle"></i> Wake Selected
        </button>

        <button id="shutdownSelectedBtn" class="btn btn-outline-danger disabled">
            <i class="bi bi-power"></i> Shutdown Selected
        </button>

        <!-- Smart Power Button -->
        <button id="smartPowerBtn" class="btn btn-outline-warning disabled">
            <i class="bi bi-lightning-charge"></i> Smart Power (Auto)
        </button>
    </div>
</div>

<input type="text" id="deviceSearch" class="form-control mb-2" placeholder="Search computers (name / IP)">

<form id="bulkActionForm" method="post" onsubmit="return confirmAction(event)">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" id="bulkAction" value="">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle" id="deviceTable">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
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
                        <td><input type="checkbox" name="device_ids[]" value="<?= $row['id'] ?>" class="deviceCheckbox"></td>
                        <td><?= e($row['name']) ?></td>
                        <td><?= ucfirst(e($row['os'])) ?></td>
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
                                       title="Wake (offline only)">
                                       <i class="bi bi-arrow-up-circle"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Shutdown -->
                                <?php if (is_admin() || !empty($row['can_shutdown'])): ?>
                                    <a data-shutdown="<?= $row['id'] ?>"
                                       href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $row['id'] ?>&csrf=<?= csrf_token() ?>"
                                       class="btn btn-outline-danger disabled"
                                       title="Shutdown (online only)">
                                       <i class="bi bi-power"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Console -->
                                <?php if (is_admin() || !empty($row['can_console'])): ?>
                                    <a data-console="<?= $row['id'] ?>"
                                       href="<?= BASE_PATH ?>/pages/console.php?device=<?= $row['id'] ?>"
                                       class="btn btn-outline-secondary disabled"
                                       title="Console (online only)">
                                       <i class="bi bi-terminal"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Admin: Edit/Delete -->
                                <?php if (is_admin()): ?>
                                    <a href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= BASE_PATH ?>/actions/device_delete.php?id=<?= $row['id'] ?>&csrf=<?= csrf_token() ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this device?')" title="Delete">
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

    <!-- Pagination -->
    <nav aria-label="Computer pagination" class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</form>

<!-- Auto status updates -->
<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>

<script>
const selectAll = document.getElementById("selectAll");
const checkboxes = document.querySelectorAll(".deviceCheckbox");
const wakeBtn = document.getElementById("wakeSelectedBtn");
const shutBtn = document.getElementById("shutdownSelectedBtn");
const smartBtn = document.getElementById("smartPowerBtn");
const form = document.getElementById("bulkActionForm");

selectAll?.addEventListener("change", function () {
    checkboxes.forEach(chk => chk.checked = this.checked);
    toggleActionButtons();
});

checkboxes.forEach(chk => chk.addEventListener("change", toggleActionButtons));

function toggleActionButtons() {
    const anyChecked = Array.from(checkboxes).some(chk => chk.checked);
    [wakeBtn, shutBtn, smartBtn].forEach(btn => {
        btn.disabled = !anyChecked;
        btn.classList.toggle("disabled", !anyChecked);
    });
}

function confirmAction(e) {
    const action = document.getElementById("bulkAction").value;
    return confirm(`Are you sure you want to ${action} the selected devices?`);
}

wakeBtn?.addEventListener("click", () => submitBulk("wake"));
shutBtn?.addEventListener("click", () => submitBulk("shutdown"));
smartBtn?.addEventListener("click", () => submitBulk("smart"));

function submitBulk(action) {
    document.getElementById("bulkAction").value = action;
    form.action = "<?= BASE_PATH ?>/actions/device_bulk_action.php";
    form.submit();
}

document.getElementById("deviceSearch").addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#deviceTable tbody tr").forEach(tr =>
        tr.style.display = tr.innerText.toLowerCase().includes(filter) ? "" : "none"
    );
});
</script>

<?php include "../layouts/footer.php"; ?>

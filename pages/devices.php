<?php
require_once __DIR__ . '/../middleware.php';
require_login();

include "../layouts/header.php";

$uid = $_SESSION['user']['id'];

/* Pagination setup */
$limit = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* Count total devices */
if (is_admin()) {
    $countResult = db_query("SELECT COUNT(*) AS total FROM devices")->get_result()->fetch_assoc();
    $total = $countResult['total'];
    $query = "SELECT * FROM devices ORDER BY type DESC, name ASC LIMIT ?, ?";
    $params = [$offset, $limit];
    $types = "ii";
} else {
    $countResult = db_query("
        SELECT COUNT(*) AS total
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE ud.user_id = ?", [$uid], "i"
    )->get_result()->fetch_assoc();
    $total = $countResult['total'];
    $query = "
        SELECT d.*
        FROM devices d
        JOIN user_devices ud ON ud.device_id = d.id
        WHERE ud.user_id = ?
        ORDER BY d.type DESC, d.name ASC
        LIMIT ?, ?";
    $params = [$uid, $offset, $limit];
    $types = "iii";
}

$result = db_query($query, $params, $types)->get_result();
$totalPages = ceil($total / $limit);
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="me-auto"><i class="bi bi-hdd-stack"></i> Devices</h3>

    <?php if (is_admin()): ?>
        <div class="btn-group">
            <a class="btn btn-primary" href="<?= BASE_PATH ?>/pages/device_form.php">
                <i class="bi bi-plus-circle"></i> Add Device
            </a>
            <button type="submit" form="bulkActionForm" id="deleteSelectedBtn" class="btn btn-danger" disabled onclick="setBulkAction('delete')">
                <i class="bi bi-trash"></i> Delete Selected
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Search -->
<input type="text" id="searchDevice" class="form-control mb-3"
       placeholder="Search devices by name / IP / MAC / OS / Type...">

<form id="bulkActionForm" method="post" action="<?= BASE_PATH ?>/actions/device_bulk_delete.php" onsubmit="return confirmAction(event)">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" id="bulkAction" value="">

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="devicesTable">
            <thead class="table-dark">
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>OS</th>
                    <th>IP</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($d = $result->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" name="device_ids[]" value="<?= $d['id'] ?>" class="deviceCheckbox"></td>
                    <td><?= e($d['name']) ?></td>
                    <td><?= ucfirst(e($d['type'])) ?></td>
                    <td><?= ucfirst(e($d['os'])) ?></td>
                    <td><?= e($d['ip']) ?></td>

                    <td>
                        <span data-device="<?= $d['id'] ?>"><span class="badge bg-secondary">...</span></span>
                        <span data-ssh="<?= $d['id'] ?>" class="ms-1"><span class="badge bg-secondary">...</span></span>
                    </td>

                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a data-wake="<?= $d['id'] ?>"
                               href="<?= BASE_PATH ?>/actions/device_wake.php?id=<?= $d['id'] ?>&csrf=<?= csrf_token() ?>"
                               class="btn btn-outline-success disabled"
                               title="Wake (offline only)">
                               <i class="bi bi-arrow-up-circle"></i>
                            </a>

                            <a data-shutdown="<?= $d['id'] ?>"
                               href="<?= BASE_PATH ?>/actions/device_shutdown.php?id=<?= $d['id'] ?>&csrf=<?= csrf_token() ?>"
                               class="btn btn-outline-danger disabled"
                               title="Shutdown (online only)">
                               <i class="bi bi-power"></i>
                            </a>

                            <a data-console="<?= $d['id'] ?>"
                               href="<?= BASE_PATH ?>/pages/console.php?device=<?= $d['id'] ?>"
                               class="btn btn-outline-secondary disabled"
                               title="Console (online only)">
                               <i class="bi bi-terminal"></i>
                            </a>

                            <?php if (is_admin()): ?>
                                <a href="<?= BASE_PATH ?>/pages/device_form.php?id=<?= $d['id'] ?>"
                                   class="btn btn-outline-primary" title="Edit">
                                   <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= BASE_PATH ?>/actions/device_delete.php?id=<?= $d['id'] ?>&csrf=<?= csrf_token() ?>"
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Delete this device?')"
                                   title="Delete Device">
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
    <nav aria-label="Device pagination" class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</form>

<script src="<?= BASE_PATH ?>/assets/js/status.js"></script>

<script>
// Search filter
document.getElementById("searchDevice").addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#devicesTable tbody tr").forEach(row =>
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none"
    );
});

// Select all
const selectAll = document.getElementById("selectAll");
const checkboxes = document.querySelectorAll(".deviceCheckbox");
const deleteBtn = document.getElementById("deleteSelectedBtn");

selectAll.addEventListener("change", function() {
    checkboxes.forEach(chk => chk.checked = this.checked);
    toggleDeleteButton();
});

checkboxes.forEach(chk => chk.addEventListener("change", toggleDeleteButton));

function toggleDeleteButton() {
    const anyChecked = document.querySelectorAll(".deviceCheckbox:checked").length > 0;
    deleteBtn.disabled = !anyChecked;
    deleteBtn.classList.toggle("disabled", !anyChecked);
}

// Set bulk action
function setBulkAction(action) {
    document.getElementById("bulkAction").value = action;
}

// Confirm bulk delete
function confirmAction(e) {
    if (document.getElementById("bulkAction").value === "delete") {
        const count = document.querySelectorAll(".deviceCheckbox:checked").length;
        if (count === 0) {
            alert("Please select at least one device to delete.");
            e.preventDefault();
            return false;
        }
        return confirm(`Are you sure you want to delete ${count} device(s)?`);
    }
    return true;
}
</script>

<?php include "../layouts/footer.php"; ?>

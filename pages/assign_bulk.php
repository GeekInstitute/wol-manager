<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

$users = db_query("SELECT id, username FROM users ORDER BY username")->get_result();
$devices = db_query("SELECT id, name, type, ip FROM devices ORDER BY type, name")->get_result();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3 rounded-top">
                    <h4 class="mb-0"><i class="bi bi-link-45deg"></i> Bulk Device Assignment</h4>
                </div>

                <form method="post" action="<?= BASE_PATH ?>/actions/assign_bulk_save.php" class="p-4">

                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                    <!-- User Select -->
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-person"></i> Select User</label>
                        <select name="user_id" id="userSelect" class="form-select" required>
                            <option value="">-- Select User --</option>
                            <?php while ($u = $users->fetch_assoc()): ?>
                                <option value="<?= $u['id'] ?>"><?= e($u['username']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Devices Multi-select -->
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-hdd-stack"></i> Select Devices</label>

                        <select name="device_ids[]" id="deviceSelect" class="form-select" multiple size="10" required>
                            <?php while ($d = $devices->fetch_assoc()): ?>
                                <option value="<?= $d['id'] ?>">
                                    <?= e($d['name']) ?> (<?= strtoupper($d['type']) ?> - <?= e($d['ip']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <small class="text-muted">Hold CTRL (Windows/Linux) or CMD (Mac) to select multiple devices.</small>
                    </div>

                    <!-- Permissions -->
                    <div class="mt-3 p-3 border rounded bg-body-secondary" id="permissionsBox" style="display:none;">
                        <label class="form-label fw-bold"><i class="bi bi-shield-check"></i> Permissions</label><br>

                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" name="can_wake" id="can_wake" value="1">
                            <label class="form-check-label">Wake</label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" name="can_shutdown" id="can_shutdown" value="1">
                            <label class="form-check-label">Shutdown</label>
                        </div>

                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="can_console" id="can_console" value="1">
                            <label class="form-check-label">Console Access</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="<?= BASE_PATH ?>/pages/assign.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button class="btn btn-primary">
                            <i class="bi bi-save"></i> Assign Devices
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


<script>
// Auto-load permissions for selected user + device
async function loadBulkPermissions() {
    const user = document.getElementById("userSelect").value;
    const devices = Array.from(document.getElementById("deviceSelect").selectedOptions).map(d => d.value);

    if (!user || devices.length === 0) return;

    document.getElementById("permissionsBox").style.display = "block";

    const res = await fetch(`<?= BASE_PATH ?>/api/get_bulk_assignment.php?user=${user}&devices=${devices.join(',')}`);
    const p = await res.json();

    document.getElementById("can_wake").checked     = p.can_wake == 1;
    document.getElementById("can_shutdown").checked = p.can_shutdown == 1;
    document.getElementById("can_console").checked  = p.can_console == 1;
}

document.getElementById("userSelect").addEventListener("change", loadBulkPermissions);
document.getElementById("deviceSelect").addEventListener("change", loadBulkPermissions);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

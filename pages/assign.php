<?php
require_once __DIR__ . '/../layouts/header.php';
ensure_admin();
?>

<h3><i class="bi bi-link"></i> Assign Devices to Users</h3>
<p class="text-muted">Select a user and assign Servers and Computers separately.</p>

<form method="post" action="<?= BASE_PATH ?>/actions/assign_save.php">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="row g-3">

        <!-- USER SELECTION -->
        <div class="col-md-4">
            <label class="form-label"><strong>User</strong></label>
            <select class="form-select" name="user_id" id="userSelect" required>
                <option value="">-- Select user --</option>
                <?php
                $users = db_query("SELECT id, username FROM users ORDER BY username")->get_result();
                while ($u = $users->fetch_assoc()):
                ?>
                    <option value="<?= e($u['id']) ?>"><?= e($u['username']) ?></option>
                <?php endwhile; ?>
            </select>

            <!-- GLOBAL PERMISSIONS -->
            <div class="mt-3">
                <label class="form-label"><strong>Permissions</strong></label><br>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="can_wake" id="permWake" value="1" checked>
                    <label class="form-check-label" for="permWake">Wake</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="can_shutdown" id="permShutdown" value="1">
                    <label class="form-check-label" for="permShutdown">Shutdown</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="can_console" id="permConsole" value="1">
                    <label class="form-check-label" for="permConsole">Console Access</label>
                </div>
            </div>
        </div>

        <!-- DEVICES AREA -->
        <div class="col-md-8">
            <div class="row">

                <!-- SERVERS -->
                <div class="col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-server"></i> Servers
                        </div>
                        <div class="card-body p-2" style="max-height: 350px; overflow-y:auto;">
                            <?php
                            $servers = db_query("SELECT id, name FROM devices WHERE type='server' ORDER BY name ASC")->get_result();
                            if ($servers->num_rows == 0): ?>
                                <p class="text-muted">No servers found.</p>
                            <?php endif; ?>

                            <?php while ($s = $servers->fetch_assoc()): ?>
                                <div class="form-check pb-1">
                                    <input class="form-check-input deviceBox" type="checkbox"
                                        name="device_ids[]" value="<?= e($s['id']) ?>" id="dev<?= e($s['id']) ?>">
                                    <label class="form-check-label" for="dev<?= e($s['id']) ?>">
                                        <?= e($s['name']) ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- COMPUTERS -->
                <div class="col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-pc-display"></i> Computers
                        </div>
                        <div class="card-body p-2" style="max-height: 350px; overflow-y:auto;">
                            <?php
                            $computers = db_query("SELECT id, name FROM devices WHERE type='computer' ORDER BY name ASC")->get_result();
                            if ($computers->num_rows == 0): ?>
                                <p class="text-muted">No computers found.</p>
                            <?php endif; ?>

                            <?php while ($c = $computers->fetch_assoc()): ?>
                                <div class="form-check pb-1">
                                    <input class="form-check-input deviceBox" type="checkbox"
                                        name="device_ids[]" value="<?= e($c['id']) ?>" id="dev<?= e($c['id']) ?>">
                                    <label class="form-check-label" for="dev<?= e($c['id']) ?>">
                                        <?= e($c['name']) ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="mt-4 text-end">
        <button class="btn btn-primary">
            <i class="bi bi-check2-circle"></i> Assign Devices
        </button>
    </div>
</form>

<!-- AUTO-LOAD ASSIGNMENTS ON USER SELECT -->
<script>
document.getElementById("userSelect").addEventListener("change", async function () {
    const user_id = this.value;

    // Clear all checkboxes first
    document.querySelectorAll(".deviceBox").forEach(cb => cb.checked = false);

    document.getElementById("permWake").checked = false;
    document.getElementById("permShutdown").checked = false;
    document.getElementById("permConsole").checked = false;

    if (!user_id) return;

    const res = await fetch("<?= BASE_PATH ?>/actions/assign_load.php?user_id=" + user_id);
    const data = await res.json();

    Object.keys(data).forEach(deviceId => {
        let box = document.getElementById("dev" + deviceId);
        if (box) box.checked = true;
    });

    if (Object.values(data)[0]) {
        let p = Object.values(data)[0];
        document.getElementById("permWake").checked = p.wake;
        document.getElementById("permShutdown").checked = p.shutdown;
        document.getElementById("permConsole").checked = p.console;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

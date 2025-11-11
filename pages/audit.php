<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin(); // only admin can see logs

include "../layouts/header.php";

// fetch logs with username + device name
$q = db_query("
    SELECT a.id, a.action, a.details, a.ip_address, a.created_at,
           u.username AS user_name,
           d.name AS device_name
    FROM audit_logs a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN devices d ON d.id = a.device_id
    ORDER BY a.id DESC
")->get_result();
?>

<div class="d-flex align-items-center mb-3">
    <h3><i class="bi bi-clipboard-data"></i> Audit Log</h3>

    <div class="ms-auto">
        <a href="<?= BASE_PATH ?>/actions/audit_export.php" class="btn btn-success btn-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>

        <a href="<?= BASE_PATH ?>/actions/audit_clear.php?csrf=<?= csrf_token() ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Are you sure you want to clear all logs?')">
            <i class="bi bi-trash"></i> Clear Logs
        </a>
    </div>
</div>

<input id="searchLog" type="text" placeholder="Search logs..." class="form-control mb-2">

<div class="table-responsive">
<table class="table table-hover table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Action</th>
            <th>Device</th>
            <th>Details</th>
            <th>IP</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody id="auditTable">
        <?php while($r = $q->fetch_assoc()): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= e($r['user_name'] ?? "SYSTEM") ?></td>
            <td><span class="badge bg-info text-dark"><?= e($r['action']) ?></span></td>
            <td><?= e($r['device_name'] ?? "-") ?></td>
            <td><?= e($r['details'] ?? "-") ?></td>
            <td><?= e($r['ip_address']) ?></td>
            <td><?= $r['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<script>
// Live search filter
document.getElementById("searchLog").addEventListener("keyup", function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll("#auditTable tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? "" : "none";
    });
});
</script>

<?php include "../layouts/footer.php"; ?>

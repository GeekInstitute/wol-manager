<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin(); // only admin can see logs

include "../layouts/header.php";

/* Pagination setup */
$limit = 50; // logs per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* Count total audit logs */
$totalRes = db_query("SELECT COUNT(*) AS total FROM audit_logs")->get_result()->fetch_assoc();
$totalLogs = $totalRes['total'] ?? 0;
$totalPages = ceil($totalLogs / $limit);

/* Fetch paginated logs with usernames and device names */
$q = db_query("
    SELECT a.id, a.action, a.details, a.ip_address, a.created_at,
           u.username AS user_name,
           d.name AS device_name
    FROM audit_logs a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN devices d ON d.id = a.device_id
    ORDER BY a.id DESC
    LIMIT ?, ?
", [$offset, $limit], "ii")->get_result();
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

<input id="searchLog" type="text" placeholder="Search logs..." class="form-control mb-3">

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
                <td><small><?= e($r['created_at']) ?></small></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Audit pagination" class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <li class="page-item <?= ($page == $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<script>
// Live search filter (within current page)
document.getElementById("searchLog").addEventListener("keyup", function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll("#auditTable tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? "" : "none";
    });
});
</script>

<?php include "../layouts/footer.php"; ?>

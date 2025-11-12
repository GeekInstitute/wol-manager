<?php
require_once __DIR__ . '/../layouts/header.php';
ensure_admin();

/* Pagination setup */
$limit = 30; // Users per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* Total count */
$totalCount = db_query("SELECT COUNT(*) AS total FROM users")->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $limit);

/* Fetch users */
$res = db_query("SELECT * FROM users ORDER BY role DESC, username ASC LIMIT ?, ?", [$offset, $limit], "ii")->get_result();
?>

<div class="d-flex align-items-center mb-3 flex-wrap">
  <h3 class="me-auto"><i class="bi bi-people"></i> Users</h3>
  <a class="btn btn-primary" href="<?= BASE_PATH ?>/pages/user_form.php">
    <i class="bi bi-person-plus"></i> Add User
  </a>
</div>

<!-- Live Search -->
<div class="input-group mb-3">
  <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
  <input type="text" id="userSearch" class="form-control" placeholder="Search users by name or role...">
</div>

<div class="table-responsive">
  <table class="table table-hover align-middle" id="usersTable">
    <thead class="table-dark">
      <tr>
        <th>Username</th>
        <th>Role</th>
        <th>Permissions</th>
        <th>Blocked Until</th>
        <th>Created</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($u = $res->fetch_assoc()): ?>
      <?php
        $isBlocked = !empty($u['is_blocked_until']) && strtotime($u['is_blocked_until']) > time();
        $rowClass = $isBlocked ? 'table-danger' : '';
      ?>
      <tr class="<?= $rowClass ?>">
        <td>
          <i class="bi bi-person-circle me-1"></i> <?= e($u['username']) ?>
          <?php if ($isBlocked): ?>
            <span class="badge bg-danger ms-1">Blocked</span>
          <?php endif; ?>
        </td>

        <td>
          <span class="badge text-bg-<?= $u['role'] == 'admin' ? 'danger' : 'secondary' ?>">
            <?= ucfirst(e($u['role'])) ?>
          </span>
        </td>

        <td>
          <span class="badge bg-primary">Servers: <?= $u['can_manage_servers'] ? 'Yes' : 'No' ?></span>
          <span class="badge bg-info text-dark">Computers: <?= $u['can_manage_computers'] ? 'Yes' : 'No' ?></span>
        </td>

        <td><small class="text-muted"><?= $u['is_blocked_until'] ?: '-' ?></small></td>
        <td><small class="text-muted"><?= e($u['created_at']) ?></small></td>

        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <a class="btn btn-outline-primary" href="<?= BASE_PATH ?>/pages/user_form.php?id=<?= $u['id'] ?>">
              <i class="bi bi-pencil-square"></i> Edit
            </a>

            <?php if ($u['username'] !== 'admin'): ?>
              <?php if ($isBlocked): ?>
                <a href="<?= BASE_PATH ?>/actions/user_unblock.php?id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>"
                   class="btn btn-outline-success"
                   onclick="return confirm('Unblock this user?')">
                   <i class="bi bi-unlock"></i> Unblock
                </a>
              <?php else: ?>
                <a href="<?= BASE_PATH ?>/actions/user_block.php?id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>"
                   class="btn btn-outline-warning"
                   onclick="return confirm('Block this user? They won’t be able to log in.')">
                   <i class="bi bi-lock"></i> Block
                </a>
              <?php endif; ?>

              <a class="btn btn-outline-danger"
                 href="<?= BASE_PATH ?>/actions/user_delete.php?id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>"
                 onclick="return confirm('Delete this user?')">
                 <i class="bi bi-trash"></i> Delete
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
<nav aria-label="User pagination" class="mt-3">
  <ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>

<script>
document.getElementById("userSearch").addEventListener("keyup", function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll("#usersTable tbody tr").forEach(tr => {
    tr.style.display = tr.innerText.toLowerCase().includes(q) ? "" : "none";
  });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

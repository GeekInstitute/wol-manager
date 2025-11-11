<?php require_once __DIR__ . '/../layouts/header.php'; ensure_admin(); ?>
<div class="d-flex align-items-center mb-3">
  <h3 class="me-auto">Users</h3>
  <a class="btn btn-primary" href="<?= BASE_PATH ?>/pages/user_form.php">Add User</a>
</div>
<?php $res=db_query("SELECT * FROM users ORDER BY role DESC, username ASC")->get_result(); ?>
<table class="table table-hover align-middle">
<thead><tr><th>Username</th><th>Role</th><th>Permissions</th><th>Blocked until</th><th>Created</th><th></th></tr></thead>
<tbody>
<?php while($u=$res->fetch_assoc()): ?>
<tr>
  <td><?= e($u['username']) ?></td>
  <td><span class="badge text-bg-<?= $u['role']=='admin'?'danger':'secondary' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
  <td>
    <span class="badge bg-primary">Servers: <?= $u['can_manage_servers']?'Yes':'No' ?></span>
    <span class="badge bg-info text-dark">Computers: <?= $u['can_manage_computers']?'Yes':'No' ?></span>
  </td>
  <td><small class="text-muted"><?= e($u['is_blocked_until'] ?: '-') ?></small></td>
  <td><small class="text-muted"><?= e($u['created_at']) ?></small></td>
  <td class="text-end">
    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_PATH ?>/pages/user_form.php?id=<?= $u['id'] ?>">Edit</a>
    <?php if ($u['username']!=='admin'): ?>
    <a class="btn btn-sm btn-outline-danger" href="<?= BASE_PATH ?>/actions/user_delete.php?id=<?= $u['id'] ?>&csrf=<?= csrf_token() ?>" onclick="return confirm('Delete user?')">Delete</a>
    <?php endif; ?>
  </td>
</tr>
<?php endwhile; ?>
</tbody></table>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

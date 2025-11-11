<?php require_once __DIR__ . '/../layouts/header.php';
$me=$_SESSION['user']; ?>
<h3>My Profile</h3>
<form class="row gy-3" method="post" action="<?= BASE_PATH ?>/actions/change_password.php">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <div class="col-md-4"><label class="form-label">Username</label>
    <input name="username" value="<?= e($me['username']) ?>" class="form-control" required>
  </div>
  <div class="col-md-4"><label class="form-label">New Password (leave blank to keep)</label>
    <input type="password" name="password" class="form-control">
  </div>
  <div class="col-12"><button class="btn btn-primary">Update</button></div>
</form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

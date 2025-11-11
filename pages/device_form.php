<?php
require_once __DIR__ . '/../layouts/header.php';
if (!is_admin()) die('Not allowed');

$id = (int)($_GET['id'] ?? 0);

$row = [
  'name' => '', 'type' => 'computer', 'os' => 'linux',
  'ip' => '', 'mac' => '', 'ssh_user' => '', 'ssh_pass' => '', 'notes' => ''
];

if ($id) {
  $s = db_query("SELECT * FROM devices WHERE id=?", [$id], "i");
  $row = $s->get_result()->fetch_assoc() ?: $row;
  $s->close();
}
?>

<h3 class="mb-4 fw-bold">
  <?= $id ? '<i class="bi bi-pencil-square"></i> Edit Device' : '<i class="bi bi-plus-circle"></i> Add Device' ?>
</h3>

<div class="card shadow-lg border-0 rounded-4">
  <div class="card-body p-4">

    <form method="post" action="<?= BASE_PATH ?>/actions/device_save.php">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="id" value="<?= $id ?>">

      <!-- Device Information -->
      <h5 class="fw-semibold"><i class="bi bi-hdd-stack"></i> Device Information</h5>
      <div class="row g-3 mt-2 mb-4 p-3 rounded bg-body-secondary">

        <div class="col-md-6">
          <label class="form-label fw-semibold">Device Name</label>
          <input name="name" class="form-control" required value="<?= e($row['name']) ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Type</label>
          <select name="type" class="form-select">
            <option value="server" <?= $row['type'] == 'server' ? 'selected' : '' ?>>Server</option>
            <option value="computer" <?= $row['type'] == 'computer' ? 'selected' : '' ?>>Computer</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">OS</label>
          <select name="os" class="form-select">
            <option value="linux" <?= $row['os'] == 'linux' ? 'selected' : '' ?>>Linux</option>
            <option value="windows" <?= $row['os'] == 'windows' ? 'selected' : '' ?>>Windows</option>
          </select>
        </div>
      </div>

      <!-- Network Settings -->
      <h5 class="fw-semibold"><i class="bi bi-globe"></i> Network Settings</h5>
      <div class="row g-3 mt-2 mb-4 p-3 rounded bg-body-secondary">

        <div class="col-md-4">
          <label class="form-label fw-semibold">IP Address</label>
          <input name="ip" class="form-control" required value="<?= e($row['ip']) ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">MAC Address</label>
          <input name="mac" class="form-control mac-input" placeholder="AA:BB:CC:DD:EE:FF"
                 required value="<?= e($row['mac']) ?>">
        </div>
      </div>

      <!-- SSH Settings -->
      <h5 class="fw-semibold"><i class="bi bi-terminal"></i> SSH Access</h5>
      <div class="row g-3 mt-2 mb-4 p-3 rounded bg-body-secondary">

        <div class="col-md-4">
          <label class="form-label fw-semibold">SSH Username</label>
          <input name="ssh_user" class="form-control" required value="<?= e($row['ssh_user']) ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">SSH Password</label>
          <div class="input-group">
            <input type="password" name="ssh_pass" id="ssh_pass" required class="form-control" value="<?= e($row['ssh_pass']) ?>">
            <button type="button" class="btn btn-outline-secondary" onclick="togglePass()">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

      </div>

      <h5 class="fw-semibold"><i class="bi bi-card-text"></i> Notes</h5>
      <textarea name="notes" class="form-control mb-3" rows="3"><?= e($row['notes']) ?></textarea>

      <!-- buttons -->
      <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary px-4">
          <i class="bi bi-check-circle"></i> Save
        </button>

        <a class="btn btn-secondary px-4" href="<?= BASE_PATH ?>/pages/devices.php">
          <i class="bi bi-x-circle"></i> Cancel
        </a>

        <?php if ($id): ?>
        <a href="<?= BASE_PATH ?>/actions/device_delete.php?id=<?= $id ?>&csrf=<?= csrf_token() ?>"
           onclick="return confirm('Delete this device?')"
           class="btn btn-danger ms-auto px-4">
           <i class="bi bi-trash"></i> Delete
        </a>
        <?php endif; ?>
      </div>

    </form>
  </div>
</div>

<script>
function togglePass() {
  let p = document.getElementById("ssh_pass");
  p.type = (p.type === "password") ? "text" : "password";
}

// Auto format MAC AA:BB:CC:DD:EE:FF
document.querySelectorAll('.mac-input').forEach(el => {
  el.addEventListener('input', () => {
    el.value = el.value.replace(/[^A-Fa-f0-9]/g, '').match(/.{1,2}/g)?.join(':').toUpperCase() ?? '';
  });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

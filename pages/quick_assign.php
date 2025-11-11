<?php require_once __DIR__ . '/../layouts/header.php'; ensure_admin(); ?>
<h3><i class="bi bi-lightning-charge"></i> Quick Assign Device</h3>

<form method="post"
      action="<?= BASE_PATH ?>/actions/assign_save.php"
      class="row gy-3 gx-3 align-items-end">

  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

  <!-- User dropdown -->
  <div class="col-md-4">
    <label class="form-label">User</label>
    <select class="form-select" name="user_id" id="userSelect" required>
      <option value="">-- Select User --</option>
      <?php
      $u = db_query("SELECT id,username FROM users ORDER BY username")->get_result();
      while ($r = $u->fetch_assoc()):
      ?>
        <option value="<?= $r['id'] ?>"><?= e($r['username']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <!-- Device dropdown -->
  <div class="col-md-4">
    <label class="form-label">Device</label>
    <select class="form-select" name="device_ids[]" id="deviceSelect" required>
      <option value="">-- Select Device --</option>
      <?php
      $d = db_query("SELECT id,name FROM devices ORDER BY name")->get_result();
      while ($x = $d->fetch_assoc()):
      ?>
        <option value="<?= $x['id'] ?>"><?= e($x['name']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <!-- Permission checkboxes -->
  <div class="col-md-4">
    <label class="form-label">Permissions</label><br>

    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="can_wake" id="can_wake" value="1">
      <label class="form-check-label">Wake</label>
    </div>

    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="can_shutdown" id="can_shutdown" value="1">
      <label class="form-check-label">Shutdown</label>
    </div>

    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="can_console" id="can_console" value="1">
      <label class="form-check-label">Console</label>
    </div>

    <button class="btn btn-primary ms-2">Assign</button>
  </div>
</form>

<script>
/* When user or device changes → fetch saved permissions and auto-check */
async function loadPermissions() {
    const user = document.getElementById("userSelect").value;
    const device = document.getElementById("deviceSelect").value;

    if (!user || !device) return;

    const res = await fetch(`<?= BASE_PATH ?>/api/get_assignment.php?user=${user}&device=${device}`);
    const p = await res.json();

    document.getElementById("can_wake").checked     = p.can_wake == 1;
    document.getElementById("can_shutdown").checked = p.can_shutdown == 1;
    document.getElementById("can_console").checked  = p.can_console == 1;
}

document.getElementById("userSelect").addEventListener("change", loadPermissions);
document.getElementById("deviceSelect").addEventListener("change", loadPermissions);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
require_once __DIR__ . '/../layouts/header.php';
ensure_admin();

$id = (int)($_GET['id'] ?? 0);
$row = ['username'=>'','role'=>'user','can_manage_servers'=>0,'can_manage_computers'=>0];

if ($id) {
    $s = db_query("SELECT * FROM users WHERE id=?", [$id], 'i');
    $row = $s->get_result()->fetch_assoc() ?: $row;
    $s->close();
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4">
                    <h4 class="mb-0 fw-semibold">
                        <i class="bi bi-person-plus"></i> <?= $id ? 'Edit User' : 'Add User' ?>
                    </h4>
                </div>

                <form method="post" action="<?= BASE_PATH ?>/actions/user_save.php" class="p-4">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input name="username" class="form-control" required value="<?= e($row['username']) ?>">
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" class="form-select">
                            <option value="user" <?= $row['role']=='user'?'selected':'' ?>>User</option>
                            <option value="admin" <?= $row['role']=='admin'?'selected':'' ?>>Admin</option>
                        </select>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Password <?= $id ? '<span class="text-muted small">(leave blank to keep existing)</span>' : '' ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control"
                                <?= $id ? '' : 'required' ?> id="passField">

                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass()">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="mt-3 p-3 border rounded bg-body-secondary">
                        <label class="form-label fw-bold"><i class="bi bi-shield-check"></i> Permissions</label>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox"
                                name="can_manage_servers" value="1"
                                <?= $row['can_manage_servers'] ? 'checked' : '' ?>>

                            <label class="form-check-label">Can manage Servers</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                name="can_manage_computers" value="1"
                                <?= $row['can_manage_computers'] ? 'checked' : '' ?>>

                            <label class="form-check-label">Can manage Computers</label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a class="btn btn-secondary" href="<?= BASE_PATH ?>/pages/users.php">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>

                        <button class="btn btn-primary">
                            <i class="bi bi-save"></i> Save User
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
function togglePass() {
    const field = document.getElementById("passField");
    const icon = document.getElementById("eyeIcon");

    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        field.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

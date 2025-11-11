<?php
require_once __DIR__ . '/../middleware.php';
require_login();
// require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$device_id = intval($_GET['device'] ?? 0);
$user_id = $_SESSION['user']['id'];

/**  DEVICE PERMISSION CHECK */
if (is_admin()) {
    $sql = "SELECT * FROM devices WHERE id=?";
    $stmt = db_query($sql, [$device_id], "i");
} else {
    $sql = "SELECT d.*, ud.can_console 
            FROM devices d
            JOIN user_devices ud ON ud.device_id = d.id
            WHERE d.id=? AND ud.user_id=? AND ud.can_console=1";
    $stmt = db_query($sql, [$device_id, $user_id], "ii");
}

$device = $stmt->get_result()->fetch_assoc();
$stmt->close();

include "../layouts/header.php";
?>

<h3><i class="bi bi-terminal"></i> SSH Console</h3>

<?php if (!$device): ?>
<div class="alert alert-info">No console access allowed for this device.</div>
<?php else: ?>

<div class="alert alert-secondary">
    <strong>Connected to:</strong> <?= e($device['name']) ?> (<?= e($device['ip']) ?>)
</div>

<div id="terminal" class="border rounded bg-dark text-light p-3"
     style="height: 400px; overflow-y:auto; font-family: monospace; font-size: 14px;">
</div>

<form id="sshForm" class="mt-2 d-flex gap-2">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="device_id" value="<?= $device['id'] ?>">

    <input type="text" id="sshCmd" name="cmd"
           class="form-control text-monospace"
           placeholder="Type command and press Enter..."
           autocomplete="off" autofocus>

    <button class="btn btn-primary">
        <i class="bi bi-send"></i>
    </button>
</form>

<script>
const term = document.getElementById("terminal");
const cmdInput = document.getElementById("sshCmd");
const form = document.getElementById("sshForm");

let history = [];
let histIndex = 0;

//  Print to terminal
function printToTerminal(text) {
    term.innerHTML += text.replace(/\n/g, "<br>") + "<br>";
    term.scrollTop = term.scrollHeight;
}

//  Listen to form submit (command execution)
form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const cmd = cmdInput.value.trim();
    if (!cmd) return;

    printToTerminal(`> <span class="text-info">${cmd}</span>`);

    history.push(cmd);
    histIndex = history.length;

    const formData = new FormData(form);

    const res = await fetch("<?= BASE_PATH ?>/api/ssh_run.php", {
        method: "POST",
        body: formData
    });

    const output = await res.text();
    printToTerminal(output);

    cmdInput.value = "";
});

//  Command history (arrow up/down)
cmdInput.addEventListener("keydown", (e) => {
    if (e.key === "ArrowUp") {
        histIndex = Math.max(histIndex - 1, 0);
        cmdInput.value = history[histIndex] || "";
    }
    else if (e.key === "ArrowDown") {
        histIndex = Math.min(histIndex + 1, history.length);
        cmdInput.value = history[histIndex] || "";
    }
});
</script>

<?php endif; ?>

<?php include "../layouts/footer.php"; ?>

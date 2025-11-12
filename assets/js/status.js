async function refreshStatus() {
  try {
    const url = `${BASE_PATH}/api/status.php?ts=${Date.now()}`;
    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const devices = await res.json();
    let anyOnline = false, anyOffline = false;

    devices.forEach(d => {
      // Dashboard + device badges
      const pingContainer = document.querySelectorAll(`[data-device="${d.id}"]`);
      pingContainer.forEach(container => {
        const badge = container.querySelector(".badge");
        if (badge) {
          badge.className = `badge ${d.online ? "bg-success" : "bg-danger"}`;
          badge.textContent = d.online ? "Online" : "Offline";
        }
      });

      // SSH badge (only on detailed pages)
      const sshContainer = document.querySelector(`[data-ssh="${d.id}"]`);
      if (sshContainer) {
        const badge = sshContainer.querySelector(".badge");
        badge.className = `badge ${d.ssh ? "bg-primary" : "bg-secondary"}`;
        badge.textContent = d.ssh ? "SSH" : "No SSH";
      }

      // Update wake/shutdown/console buttons
      const wakeBtn = document.querySelector(`[data-wake="${d.id}"]`);
      const shutBtn = document.querySelector(`[data-shutdown="${d.id}"]`);
      const consoleBtn = document.querySelector(`[data-console="${d.id}"]`);

      if (d.online) anyOnline = true; else anyOffline = true;

      if (wakeBtn) {
        wakeBtn.disabled = d.online;
        wakeBtn.classList.toggle("disabled", d.online);
      }

      if (shutBtn) {
        shutBtn.disabled = !d.online;
        shutBtn.classList.toggle("disabled", !d.online);
      }

      if (consoleBtn) {
        const enable = d.online && d.ssh;
        consoleBtn.disabled = !enable;
        consoleBtn.classList.toggle("disabled", !enable);
      }
    });

    // Wake All / Shutdown All
    const wakeAllBtn = document.querySelector("[data-wake-all]");
    const shutAllBtn = document.querySelector("[data-shutdown-all]");
    if (wakeAllBtn) {
      wakeAllBtn.disabled = !anyOffline;
      wakeAllBtn.classList.toggle("disabled", !anyOffline);
    }
    if (shutAllBtn) {
      shutAllBtn.disabled = !anyOnline;
      shutAllBtn.classList.toggle("disabled", !anyOnline);
    }

  } catch (err) {
    console.error("Status fetch failed:", err);
  }
}

setInterval(refreshStatus, 5000);
document.addEventListener("DOMContentLoaded", refreshStatus);

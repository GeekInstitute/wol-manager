async function refreshStatus() {
    try {
        const url = `${BASE_PATH}/api/status.php?ts=${Date.now()}`;
        const res = await fetch(url, { cache: "no-store" });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const devices = await res.json();

        let anyOnline = false;
        let anyOffline = false;

        devices.forEach(d => {
            const pingBadge  = document.querySelector(`[data-device="${d.id}"] .badge`);
            const sshBadge   = document.querySelector(`[data-ssh="${d.id}"] .badge`);
            const wakeBtn    = document.querySelector(`[data-wake="${d.id}"]`);
            const shutBtn    = document.querySelector(`[data-shutdown="${d.id}"]`);
            const consoleBtn = document.querySelector(`[data-console="${d.id}"]`);

            if (!pingBadge || !sshBadge) return;

            // Status badges update
            pingBadge.className = `badge ${d.online ? "bg-success" : "bg-danger"}`;
            pingBadge.textContent = d.online ? "Online" : "Offline";

            sshBadge.className  = `badge ${d.ssh ? "bg-primary" : "bg-secondary"}`;
            sshBadge.textContent = d.ssh ? "SSH" : "No SSH";

            // Determine global button state
            if (d.online) anyOnline = true;
            else anyOffline = true;

            // Wake button (offline only)
            if (wakeBtn) {
                wakeBtn.classList.toggle("disabled", d.online);
            }

            // Shutdown button (online only)
            if (shutBtn) {
                shutBtn.classList.toggle("disabled", !d.online);
            }

            // Console button (online + ssh)
            if (consoleBtn) {
                const enable = d.online && d.ssh;
                consoleBtn.classList.toggle("disabled", !enable);
                consoleBtn.setAttribute("aria-disabled", enable ? "false" : "true");
            }
        });

        // Enable/Disable Wake All / Shutdown All
        document.querySelector("[data-wake-all]")?.classList.toggle("disabled", !anyOffline);
        document.querySelector("[data-shutdown-all]")?.classList.toggle("disabled", !anyOnline);

    } catch (err) {
        console.error("Status fetch failed:", err);
    }
}

setInterval(refreshStatus, 8000);
document.addEventListener("DOMContentLoaded", refreshStatus);

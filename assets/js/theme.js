(function () {
  const key = "wol-theme";
  const stored = localStorage.getItem(key);
  const root = document.documentElement;

  function setTheme(mode) {
    root.setAttribute("data-bs-theme", mode);
    localStorage.setItem(key, mode);
  }

  // initial
  if (stored === "light" || stored === "dark") {
    setTheme(stored);
  } else {
    setTheme("light");
  }

  window.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("themeToggle");
    if (!btn) return;
    btn.addEventListener("click", () => {
      const current = root.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
      setTheme(current);
    });
  });
})();

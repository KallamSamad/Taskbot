 

(() => {
  const MODES = [
    { key: "a11y-dark",       btnId: "btnDark" },
    { key: "a11y-large-text", btnId: "btnLargeText" },
    { key: "a11y-contrast",   btnId: "btnContrast" }
  ];

  const syncBtn = (btn, enabled) => {
    btn.setAttribute("aria-pressed", String(enabled));
    btn.textContent = enabled ? "On" : "Off";
  };

  const setMode = (key, enabled) => {
    localStorage.setItem(key, enabled ? "true" : "false");
    document.body.classList.toggle(key, enabled);
  };

  const applySaved = () => {
    MODES.forEach(({ key }) => {
      const enabled = localStorage.getItem(key) === "true";
      document.body.classList.toggle(key, enabled);
    });
  };

  const wire = () => {
    let foundAny = false;

    MODES.forEach(({ key, btnId }) => {
      const btn = document.getElementById(btnId);
      const enabled = document.body.classList.contains(key);

      if (!btn) {
        console.warn("[A11Y] Missing button id:", btnId);
        return;
      }

      foundAny = true;
      syncBtn(btn, enabled);

      btn.addEventListener("click", () => {
        const now = document.body.classList.contains(key);
        const next = !now;
        setMode(key, next);
        syncBtn(btn, next);
        console.log("[A11Y] Toggled", key, "=>", next);
      });
    });

    const reset = document.getElementById("btnResetA11y");
    if (reset) {
      foundAny = true;
      reset.addEventListener("click", () => {
        MODES.forEach(m => setMode(m.key, false));
        MODES.forEach(({ key, btnId }) => {
          const btn = document.getElementById(btnId);
          if (btn) syncBtn(btn, false);
        });
        console.log("[A11Y] Reset all");
      });
    } else {
      console.warn("[A11Y] Missing reset button id: btnResetA11y");
    }

    if (!foundAny) {
      console.warn("[A11Y] No buttons found on this page. (That’s OK on non-settings pages.)");
    }
  };

  document.addEventListener("DOMContentLoaded", () => {
    console.log("A11Y DOM READY ");
    applySaved();
    wire();
  });
})();
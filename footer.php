<div class="footer">By Kallam Samad 2026</div>
 <script>
function toggleMode(mode) {
  if (mode === "a11y-dark") {
    document.body.classList.remove("a11y-contrast");
    localStorage.removeItem("a11y-contrast");
  }

  if (mode === "a11y-contrast") {
    document.body.classList.remove("a11y-dark");
    localStorage.removeItem("a11y-dark");
  }

  if (document.body.classList.contains(mode)) {
    document.body.classList.remove(mode);
    localStorage.removeItem(mode);
  } else {
    document.body.classList.add(mode);
    localStorage.setItem(mode, "true");
  }
}

window.onload = function() {
  if (localStorage.getItem("a11y-dark") === "true") {
    document.body.classList.add("a11y-dark");
  }

  if (localStorage.getItem("a11y-large-text") === "true") {
    document.body.classList.add("a11y-large-text");
  }

  if (localStorage.getItem("a11y-contrast") === "true") {
    document.body.classList.add("a11y-contrast");
  }
};
</script>
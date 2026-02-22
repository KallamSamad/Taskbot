<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Accessibility</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

   <link rel="stylesheet" href="/TaskBot/style.css">

   <link rel="stylesheet" href="/TaskBot/a11y.css">

 
  <script>
    (function(){
      const keys=["a11y-dark","a11y-large-text","a11y-contrast"];
 
    })();
  </script>
</head>

<body class="p-4">
  <div class="container" style="max-width: 760px;">
    <h1 class="mb-3">Accessibility Settings</h1>
    <p class="text-muted">Toggle modes. They persist across TaskBot pages.</p>

    <div class="card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="fw-bold">Dark mode</div>
          <small>Black &amp; white only</small>
        </div>
        <button id="btnDark" type="button" class="btn btn-outline-dark" aria-pressed="false">Off</button>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="fw-bold">Large text</div>
          <small>Very big scaling</small>
        </div>
        <button id="btnLargeText" type="button" class="btn btn-outline-dark" aria-pressed="false">Off</button>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <div class="fw-bold">High contrast</div>
          <small>Thick borders + underlined links</small>
        </div>
        <button id="btnContrast" type="button" class="btn btn-outline-dark" aria-pressed="false">Off</button>
      </div>

      <button id="btnResetA11y" type="button" class="btn btn-danger">Reset (turn all off)</button>
    </div>

    <div class="mt-4 d-flex gap-2 flex-wrap">
      <a class="btn btn-success" href="/TaskBot/index.php?page=home">Back to TaskBot</a>
    </div>
  </div>
 
  <script defer src="/TaskBot/a11y.js"></script>
</body>
</html>
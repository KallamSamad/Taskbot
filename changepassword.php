<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['userId'])) {
  header("Location: index.php");
  exit;
}

$userId = (int)$_SESSION['userId'];
$msg = "";
$err = "";

function clean($v){ return trim((string)$v); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current = clean($_POST['current_password'] ?? "");
  $new     = clean($_POST['new_password'] ?? "");
  $confirm = clean($_POST['confirm_password'] ?? "");

  if ($current === "" || $new === "" || $confirm === "") {
    $err = "Please fill in all fields.";
  } elseif ($new !== $confirm) {
    $err = "New password and confirmation do not match.";
  } elseif (strlen($new) < 8) {
    $err = "New password must be at least 8 characters.";
  } elseif (!preg_match('/[A-Z]/', $new) || !preg_match('/\d/', $new) || !preg_match('/[^A-Za-z0-9]/', $new)) {
    $err = "New password must include an uppercase letter, a number, and a special character.";
  } else {
    $stmt = $db->prepare("
      SELECT HashedPassword
      FROM Credentials
      WHERE UserID = :uid
      LIMIT 1
    ");
    $stmt->bindValue(":uid", $userId, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);

    $res->finalize();
    $stmt->close();

    if (!$row) {
      $err = "Credentials not found for this user.";
    } elseif (!password_verify($current, $row['HashedPassword'])) {
      $err = "Current password is incorrect.";
    } else {
      $newHash = password_hash($new, PASSWORD_DEFAULT);

      $stmt2 = $db->prepare("
        UPDATE Credentials
        SET HashedPassword = :hp,
            PasswordChangedAt = CURRENT_TIMESTAMP
        WHERE UserID = :uid
      ");
      $stmt2->bindValue(":hp", $newHash, SQLITE3_TEXT);
      $stmt2->bindValue(":uid", $userId, SQLITE3_INTEGER);

      $ok = $stmt2->execute();
      $stmt2->close();

      if ($ok) {
        $msg = "Password changed successfully.";
      } else {
        $err = "Could not update password. Please try again.";
      }
    }
  }
}

$db->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Change Password</title>

  <script>
  (function(){
    const keys=["a11y-dark","a11y-large-text","a11y-contrast"];
    const root=document.documentElement;
    for(const k of keys){
      root.classList.toggle(k, localStorage.getItem(k)==="true");
    }
  })();
  </script>

  <link rel="stylesheet" href="/TaskBot/style.css">
  <link rel="stylesheet" href="/TaskBot/a11y.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
  <div class="container" style="max-width: 720px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="m-0">Change Password</h1>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="/TaskBot/viewprofile.php">View Profile</a>
        <a class="btn btn-success" href="/TaskBot/index.php?page=home">Back to TaskBot</a>
      </div>
    </div>

    <?php if ($msg !== ""): ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($err !== ""): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <div class="card p-4">
      <form method="POST" class="row g-3" novalidate>
        <div class="col-12">
          <label class="form-label">Current password</label>
          <input class="form-control" type="password" name="current_password" required>
        </div>

        <div class="col-12">
          <label class="form-label">New password</label>
          <input class="form-control" type="password" name="new_password"
                 pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                 required
                 oninvalid="this.setCustomValidity('Password must be at least 8 characters and include an uppercase letter, a number, and a special character.')"
                 oninput="this.setCustomValidity('')">
          <div class="form-text">Min 8 chars, 1 uppercase, 1 number, 1 special.</div>
        </div>

        <div class="col-12">
          <label class="form-label">Confirm new password</label>
          <input class="form-control" type="password" name="confirm_password" required>
        </div>

        <div class="col-12 d-flex gap-2 flex-wrap mt-2">
          <button class="btn btn-dark" type="submit">Update Password</button>
          <a class="btn btn-outline-secondary" href="/TaskBot/index.php?page=home">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <script defer src="/TaskBot/a11y.js"></script>
</body>
</html>
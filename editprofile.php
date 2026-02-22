<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['userId'])) {
  header("Location: index.php");
  exit;
}

$userId = (int)$_SESSION['userId'];
$message = "";

function clean($v) {
  return trim((string)$v);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $displayName = clean($_POST['displayName'] ?? "");
  $firstName   = clean($_POST['firstName'] ?? "");
  $middleName  = clean($_POST['middleName'] ?? "");
  $lastName    = clean($_POST['lastName'] ?? "");
  $email       = clean($_POST['email'] ?? "");
  $phone       = clean($_POST['phone'] ?? "");

  if ($displayName === "" || $firstName === "" || $lastName === "" || $email === "") {
    $message = "Please fill in Display Name, First Name, Last Name, and Email.";
  } else {
    $stmt = $db->prepare("
      UPDATE Users
      SET DisplayName = :dn,
          FirstName   = :fn,
          MiddleName  = :mn,
          LastName    = :ln,
          Email       = :em,
          Phone       = :ph
      WHERE UserID = :uid
    ");
    $stmt->bindValue(":dn", $displayName, SQLITE3_TEXT);
    $stmt->bindValue(":fn", $firstName, SQLITE3_TEXT);
    $stmt->bindValue(":mn", $middleName, SQLITE3_TEXT);
    $stmt->bindValue(":ln", $lastName, SQLITE3_TEXT);
    $stmt->bindValue(":em", $email, SQLITE3_TEXT);
    $stmt->bindValue(":ph", $phone !== "" ? $phone : null, SQLITE3_TEXT);
    $stmt->bindValue(":uid", $userId, SQLITE3_INTEGER);

    $ok = $stmt->execute();
    if ($ok) {
      $message = "Profile updated successfully.";
    } else {
      $message = "Update failed. Please try again.";
    }
    $stmt->close();
  }
}

$stmt2 = $db->prepare("
  SELECT DisplayName, FirstName, MiddleName, LastName, Email, Phone
  FROM Users
  WHERE UserID = :uid
  LIMIT 1
");
$stmt2->bindValue(":uid", $userId, SQLITE3_INTEGER);
$res2 = $stmt2->execute();
$user = $res2->fetchArray(SQLITE3_ASSOC);

$res2->finalize();
$stmt2->close();
$db->close();

if (!$user) {
  echo "User not found.";
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Profile</title>

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
  <div class="container" style="max-width: 820px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="m-0">Edit Profile</h1>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="/TaskBot/viewprofile.php">View Profile</a>
        <a class="btn btn-success" href="/TaskBot/index.php?page=home">Back to TaskBot</a>
      </div>
    </div>

    <?php if ($message !== ""): ?>
      <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card p-4">
      <form method="POST" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Display Name</label>
          <input class="form-control" name="displayName" required value="<?= htmlspecialchars($user['DisplayName'] ?? '') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars($user['Email'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label">First Name</label>
          <input class="form-control" name="firstName" required value="<?= htmlspecialchars($user['FirstName'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label">Middle Name</label>
          <input class="form-control" name="middleName" value="<?= htmlspecialchars($user['MiddleName'] ?? '') ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label">Last Name</label>
          <input class="form-control" name="lastName" required value="<?= htmlspecialchars($user['LastName'] ?? '') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input class="form-control" type="tel" name="phone" value="<?= htmlspecialchars($user['Phone'] ?? '') ?>">
        </div>

        <div class="col-12 d-flex gap-2 flex-wrap mt-2">
          <button class="btn btn-dark" type="submit">Save Changes</button>
          <a class="btn btn-outline-secondary" href="/TaskBot/viewprofile.php">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <script defer src="/TaskBot/a11y.js"></script>
</body>
</html>
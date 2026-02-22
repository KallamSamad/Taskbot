 <?php
session_start();
require_once "db.php";

if (!isset($_SESSION['userId'])) {
  header("Location: index.php");
  exit;
}

$userId = (int)$_SESSION['userId'];

$stmt = $db->prepare("
  SELECT UserID, DisplayName, FirstName, MiddleName, LastName, Email, Phone, IsActive, LastLoginAt
  FROM Users
  WHERE UserID = :uid
  LIMIT 1
");
$stmt->bindValue(":uid", $userId, SQLITE3_INTEGER);
$res = $stmt->execute();
$user = $res->fetchArray(SQLITE3_ASSOC);

$res->finalize();
$stmt->close();
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
  <title>View Profile</title>

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
      <h1 class="m-0">My Profile</h1>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="/TaskBot/editprofile.php">Edit Profile</a>
        <a class="btn btn-success" href="/TaskBot/index.php?page=home">Back to TaskBot</a>
      </div>
    </div>

    <div class="card p-4">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="fw-bold">Display Name</div>
          <div><?= htmlspecialchars($user['DisplayName'] ?? '') ?></div>
        </div>

        <div class="col-md-6">
          <div class="fw-bold">Username</div>
          <div><?= htmlspecialchars($_SESSION['username'] ?? '') ?></div>
        </div>

        <div class="col-md-4">
          <div class="fw-bold">First Name</div>
          <div><?= htmlspecialchars($user['FirstName'] ?? '') ?></div>
        </div>

        <div class="col-md-4">
          <div class="fw-bold">Middle Name</div>
          <div><?= htmlspecialchars($user['MiddleName'] ?? '') ?></div>
        </div>

        <div class="col-md-4">
          <div class="fw-bold">Last Name</div>
          <div><?= htmlspecialchars($user['LastName'] ?? '') ?></div>
        </div>

        <div class="col-md-6">
          <div class="fw-bold">Email</div>
          <div><?= htmlspecialchars($user['Email'] ?? '') ?></div>
        </div>

        <div class="col-md-6">
          <div class="fw-bold">Phone</div>
          <div><?= htmlspecialchars($user['Phone'] ?? '') ?></div>
        </div>

        <div class="col-md-6">
          <div class="fw-bold">Account Status</div>
          <div><?= ((int)($user['IsActive'] ?? 1) === 1) ? "Active" : "Inactive" ?></div>
        </div>

        <div class="col-md-6">
          <div class="fw-bold">Last Login</div>
          <div><?= htmlspecialchars($user['LastLoginAt'] ?? '') ?></div>
        </div>
      </div>
    </div>
  </div>

  <script defer src="/TaskBot/a11y.js"></script>
</body>
</html>
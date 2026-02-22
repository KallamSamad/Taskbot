<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? null;

if ($username === null) {
    $displayname = "Guest";
    $roleType = "Unknown Role";
} else {
    $stmt = $db->prepare("
        SELECT
            U.DisplayName,
            R.RoleType
        FROM Credentials AS C
        INNER JOIN Users AS U ON C.UserID = U.UserID
        INNER JOIN Role  AS R ON U.RoleID = R.RoleID
        WHERE C.Username = :username
        LIMIT 1
    ");

    $stmt->bindValue(":username", $username, SQLITE3_TEXT);

    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    $result->finalize();
    $stmt->close();

    $displayname = $row["DisplayName"] ?? "Unknown Name";
    $roleType = $row["RoleType"] ?? "Unknown Role";
}
?>

<div class="hamburger">
  <div class="profile-info">
    <span>
      Welcome <?= htmlspecialchars($displayname) ?>
      (<?= htmlspecialchars($roleType) ?>)
    </span>
  </div>
</div>

<div class="dashboard">
  <ul>
    <li><a href="/TaskBot/viewprofile.php">View Profile</a></li>
    <li><a href="/TaskBot/editprofile.php">Edit Profile</a></li>
    <li><a href="/TaskBot/changepassword.php">Change Password</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</div>
<?php
require_once "db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    $username = null;
} else {
    $username = $_SESSION['username'];
}

$username=$_SESSION['username'];

$role = $_SESSION['role'] ?? 'Unknown Role';

$stmt =$db->prepare("SELECT C.UserID,
       U.RoleID,
       U.DisplayName,
       R.RoleType
FROM Credentials AS C
INNER JOIN Users AS U ON C.UserID = U.UserID
INNER JOIN Role AS R ON U.RoleID = R.RoleID
WHERE C.Username = :username;");
$stmt->bindValue(":username",$username,SQLITE3_TEXT);
$result=$stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
$id=$row["RoleType"] ?? "Unknown Role";
$displayname=$row["DisplayName"]?? "Unknown Name";
?>

<div class="hamburger">
  <div class="profile-info">
    <span>
      Welcome <?= $displayname ?>
      (<?= htmlspecialchars($id) ?>)
    </span>

  </div>
</div>

<div class="dashboard">
  <ul>
    <li><a href="viewProfile.php">View Profile</a></li>
    <li><a href="updateProfile.php">Edit Profile</a></li>
    <li><a href="updatePassword.php">Change Password</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</div>

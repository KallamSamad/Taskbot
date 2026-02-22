<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: index.php');
    exit();
}

require_once 'db.php';
$db->exec('PRAGMA foreign_keys = ON;');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['UserID'])) {
    header('Location: index.php?page=manageusers');
    exit();
}

$targetUserId = (int)$_POST['UserID'];
$adminId      = (int)$_SESSION['userId'];

if ($targetUserId <= 0) {
    header('Location: index.php?page=manageusers');
    exit();
}
 
if ($targetUserId === $adminId) {
    $_SESSION['flash'] = 'You cannot delete your own account';
    header('Location: index.php?page=manageusers');
    exit();
}

 
$roleStmt = $db->prepare("
    SELECT R.RoleType
    FROM Users U
    INNER JOIN Role R ON R.RoleID = U.RoleID
    WHERE U.UserID = :id
    LIMIT 1
");
$roleStmt->bindValue(':id', $targetUserId, SQLITE3_INTEGER);
$roleRes = $roleStmt->execute();
$roleRow = $roleRes->fetchArray(SQLITE3_ASSOC);
$roleRes->finalize();
$roleStmt->close();

if (!$roleRow) {
    $_SESSION['flash'] = 'User not found';
    header('Location: index.php?page=manageusers');
    exit();
}

if (($roleRow['RoleType'] ?? '') === 'Admin') {
    $_SESSION['flash'] = 'You cannot delete an admin account';
    header('Location: index.php?page=manageusers');
    exit();
}
 
$stmt = $db->prepare('DELETE FROM Credentials WHERE UserID = :id');
$stmt->bindValue(':id', $targetUserId, SQLITE3_INTEGER);
$stmt->execute();
$stmt->close();
 
$stmt = $db->prepare('DELETE FROM Users WHERE UserID = :id');
$stmt->bindValue(':id', $targetUserId, SQLITE3_INTEGER);
$res = $stmt->execute();
$stmt->close();

$_SESSION['flash'] = ($res === false) ? 'Error deleting user' : 'User deleted';

header('Location: index.php?page=manageusers');
exit();
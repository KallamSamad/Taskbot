<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.php';
$db->exec('PRAGMA foreign_keys = ON;');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['TaskID'])) {
    header('Location: index.php?page=tasks');
    exit();
}

$userId = (int)$_SESSION['userId'];
$role   = (string)($_SESSION['role'] ?? 'Staff');
$taskId = (int)$_POST['TaskID'];

if ($taskId <= 0) {
    header('Location: index.php?page=tasks');
    exit();
}

if ($role === 'Admin') {
    $stmt = $db->prepare('DELETE FROM Task WHERE TaskID = :id');
    $stmt->bindValue(':id', $taskId, SQLITE3_INTEGER);
} else {
    $stmt = $db->prepare('DELETE FROM Task WHERE TaskID = :id AND LastUpdatedBy = :userId');
    $stmt->bindValue(':id', $taskId, SQLITE3_INTEGER);
    $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
}

$res = $stmt->execute();
$stmt->close();

if ($res !== false) {
    $db->exec("PRAGMA foreign_keys = ON;");
}

$_SESSION['flash'] = 'Task deleted';

if ($role === 'Admin') {
    header('Location: index.php?page=alltasks');
} else {
    header('Location: index.php?page=tasks');
}
exit();
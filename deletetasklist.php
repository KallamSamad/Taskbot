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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['TaskListID'])) {
    header('Location: index.php?page=lists');
    exit();
}

$userId = (int)$_SESSION['userId'];
$role   = (string)($_SESSION['role'] ?? 'Staff');

$listId = (int)$_POST['TaskListID'];

if ($role === 'Admin') {
    $stmt = $db->prepare("
        UPDATE TaskList
        SET IsArchived = 1,
            UpdatedAt = CURRENT_TIMESTAMP
        WHERE TaskListID = :id
          AND IsArchived = 0
    ");
    $stmt->bindValue(':id', $listId, SQLITE3_INTEGER);
    $back = 'index.php?page=alltasklists';
} else {
    $stmt = $db->prepare("
        UPDATE TaskList
        SET IsArchived = 1,
            UpdatedAt = CURRENT_TIMESTAMP
        WHERE TaskListID = :id
          AND LastUpdatedBy = :ownerId
          AND IsArchived = 0
    ");
    $stmt->bindValue(':id', $listId, SQLITE3_INTEGER);
    $stmt->bindValue(':ownerId', $userId, SQLITE3_INTEGER);
    $back = 'index.php?page=lists';
}

$res = $stmt->execute();
$stmt->close();

if ($res === false) {
    $_SESSION['flash'] = 'Error deleting task list';
} else {
    if ($db->changes() === 0) {
        $_SESSION['flash'] = 'Task list not found';
    } else {
        $_SESSION['flash'] = 'Task list deleted';
    }
}

header("Location: {$back}");
exit();
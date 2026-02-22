<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
    header('Location: index.php');
    exit();
}

$db->exec('PRAGMA foreign_keys = ON;');

$userId = (int)$_SESSION['userId'];
$role   = (string)($_SESSION['role'] ?? 'Staff');

if (!isset($_POST['TaskListID']) && !isset($_GET['TaskListID'])) {
    $_SESSION['flash'] = 'No task list selected';
    header('Location: ' . (($role === 'Admin') ? 'index.php?page=alltasklists' : 'index.php?page=lists'));
    exit();
}

$listId  = isset($_POST['TaskListID']) ? (int)$_POST['TaskListID'] : (int)$_GET['TaskListID'];
$message = '';

/* SAVE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveTaskList'])) {

    $title    = trim((string)($_POST['title'] ?? ''));
    $desc     = trim((string)($_POST['desc'] ?? ''));
    $priority = (string)($_POST['priority'] ?? 'Low');
    $status   = (string)($_POST['status'] ?? 'Pending');
    $dueDate  = $_POST['dueDate'] ?? '';

    if ($dueDate === '') $dueDate = null;

    if ($title === '' || $desc === '') {
        $message = "<p class='text-danger'>Title and description are required.</p>";
    } else {

        $completionDate = null;
        if ($status === 'Complete') {
            $completionDate = date('Y-m-d');
        }

        if ($role === 'Admin') {
            $stmt = $db->prepare("
                UPDATE TaskList
                SET ListTitle = :title,
                    Description = :desc,
                    Priority = :priority,
                    Status = :status,
                    DueDate = :dueDate,
                    CompletionDate = :completionDate,
                    UpdatedAt = CURRENT_TIMESTAMP
                WHERE TaskListID = :id
                  AND IsArchived = 0
            ");
            $stmt->bindValue(':id', $listId, SQLITE3_INTEGER);
        } else {
            $stmt = $db->prepare("
                UPDATE TaskList
                SET ListTitle = :title,
                    Description = :desc,
                    Priority = :priority,
                    Status = :status,
                    DueDate = :dueDate,
                    CompletionDate = :completionDate,
                    UpdatedAt = CURRENT_TIMESTAMP
                WHERE TaskListID = :id
                  AND LastUpdatedBy = :ownerId
                  AND IsArchived = 0
            ");
            $stmt->bindValue(':id', $listId, SQLITE3_INTEGER);
            $stmt->bindValue(':ownerId', $userId, SQLITE3_INTEGER);
        }

        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':desc', $desc, SQLITE3_TEXT);
        $stmt->bindValue(':priority', $priority, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);

        if ($dueDate === null) $stmt->bindValue(':dueDate', null, SQLITE3_NULL);
        else $stmt->bindValue(':dueDate', $dueDate, SQLITE3_TEXT);

        if ($completionDate === null) $stmt->bindValue(':completionDate', null, SQLITE3_NULL);
        else $stmt->bindValue(':completionDate', $completionDate, SQLITE3_TEXT);

        $res = $stmt->execute();
        $stmt->close();

        if ($res === false) {
            $message = "<p class='text-danger'>DB error: " . htmlspecialchars($db->lastErrorMsg()) . "</p>";
        } else {
            if ($db->changes() === 0) {
                $_SESSION['flash'] = 'Task list not found';
                header('Location: index.php?page=lists');
                exit();
            }
            $message = "<p class='text-success'>Task list updated</p>";
        }
    }
}

/* LOAD */
if ($role === 'Admin') {
    $stmt = $db->prepare("
        SELECT TaskListID, ListTitle, Description, DueDate, Priority, Status
        FROM TaskList
        WHERE TaskListID = :id
          AND IsArchived = 0
        LIMIT 1
    ");
    $stmt->bindValue(':id', $listId, SQLITE3_INTEGER);
} else {
    $stmt = $db->prepare("
        SELECT TaskListID, ListTitle, Description, DueDate, Priority, Status
        FROM TaskList
        WHERE TaskListID = :id
          AND LastUpdatedBy = :ownerId
          AND IsArchived = 0
        LIMIT 1
    ");
    $stmt->bindValue(':id', $listId, SQLITE3_INTEGER);
    $stmt->bindValue(':ownerId', $userId, SQLITE3_INTEGER);
}

$resList = $stmt->execute();
$list = $resList->fetchArray(SQLITE3_ASSOC);
$resList->finalize();
$stmt->close();

if (!$list) {
    $_SESSION['flash'] = 'Task list not found';
    header('Location: ' . (($role === 'Admin') ? 'index.php?page=alltasklists' : 'index.php?page=lists'));
    exit();
}

$titleVal  = htmlspecialchars($list['ListTitle'] ?? '');
$descVal   = htmlspecialchars($list['Description'] ?? '');
$dueVal    = htmlspecialchars((string)($list['DueDate'] ?? ''));
$prioVal   = (string)($list['Priority'] ?? 'Low');
$statusVal = (string)($list['Status'] ?? 'Pending');

$backUrl = ($role === 'Admin') ? 'index.php?page=alltasklists' : 'index.php?page=lists';
?>

<h1 class="page-title">Update Task List</h1>

<?php echo $message; ?>

<form class="addform" method="POST">
    <input type="hidden" name="TaskListID" value="<?php echo (int)$listId; ?>">

    <label>List Title</label>
    <input name="title" value="<?php echo $titleVal; ?>" required>

    <label>Description</label>
    <textarea name="desc" required><?php echo $descVal; ?></textarea>

    <label>Priority</label>
    <select name="priority">
        <option value="Low" <?php echo ($prioVal === 'Low') ? 'selected' : ''; ?>>Low</option>
        <option value="Medium" <?php echo ($prioVal === 'Medium') ? 'selected' : ''; ?>>Medium</option>
        <option value="High" <?php echo ($prioVal === 'High') ? 'selected' : ''; ?>>High</option>
    </select>

    <label>Status</label>
    <select name="status">
        <option value="Pending" <?php echo ($statusVal === 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value="Complete" <?php echo ($statusVal === 'Complete') ? 'selected' : ''; ?>>Complete</option>
        <option value="Cancelled" <?php echo ($statusVal === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
    </select>

    <label>Due Date (optional)</label>
    <input type="date" name="dueDate" value="<?php echo $dueVal; ?>">

    <button name="saveTaskList" type="submit">Save</button>
    <a class="btn" style="text-decoration:none; text-align:center;" href="<?php echo $backUrl; ?>">Back</a>
</form>
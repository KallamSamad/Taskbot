<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
    echo 'Not logged in';
    exit();
}

require_once 'db.php';
$db->exec('PRAGMA foreign_keys = ON;');

$userId = (int)$_SESSION['userId'];
$role   = (string)($_SESSION['role'] ?? 'Staff');
$username = (string)($_SESSION['username'] ?? '');

if (!isset($_POST['TaskID']) && !isset($_GET['TaskID'])) {
    echo "<p class='text-danger'>No task selected.</p>";
    exit();
}

$targetTaskId = isset($_POST['TaskID']) ? (int)$_POST['TaskID'] : (int)$_GET['TaskID'];
$message = '';

/* ---------- Save update ---------- */
if (isset($_POST['saveTask'])) {

    $title    = trim((string)($_POST['title'] ?? ''));
    $desc     = trim((string)($_POST['desc'] ?? ''));
    $priority = (string)($_POST['priority'] ?? 'Low');
    $status   = (string)($_POST['status'] ?? 'Pending');
    $taskList = $_POST['TaskListID'] ?? null;
    $dueDate  = $_POST['dueDate'] ?? '';

    if ($taskList === '') {
        $taskList = null;
    } else if ($taskList !== null) {
        $taskList = (int)$taskList;
    }

    if ($dueDate === '') {
        $dueDate = null;
    }

    if ($title === '' || $desc === '') {
        $message = "<p class='text-danger'>Title and description are required.</p>";
    } else {

        // Staff can only update their own tasks
        if ($role === 'Admin') {
            $update = $db->prepare("
                UPDATE Task
                SET TaskTitle = :title,
                    Description = :desc,
                    Priority = :priority,
                    Status = :status,
                    DueDate = :dueDate,
                    TaskListID = :taskList,
                    LastUpdatedBy = :updatedBy
                WHERE TaskID = :taskId
            ");
        } else {
            $update = $db->prepare("
                UPDATE Task
                SET TaskTitle = :title,
                    Description = :desc,
                    Priority = :priority,
                    Status = :status,
                    DueDate = :dueDate,
                    TaskListID = :taskList,
                    LastUpdatedBy = :updatedBy
                WHERE TaskID = :taskId
                  AND LastUpdatedBy = :ownerId
            ");
            $update->bindValue(':ownerId', $userId, SQLITE3_INTEGER);
        }

        $update->bindValue(':title', $title, SQLITE3_TEXT);
        $update->bindValue(':desc', $desc, SQLITE3_TEXT);
        $update->bindValue(':priority', $priority, SQLITE3_TEXT);
        $update->bindValue(':status', $status, SQLITE3_TEXT);

        if ($dueDate === null) {
            $update->bindValue(':dueDate', null, SQLITE3_NULL);
        } else {
            $update->bindValue(':dueDate', $dueDate, SQLITE3_TEXT);
        }

        if ($taskList === null) {
            $update->bindValue(':taskList', null, SQLITE3_NULL);
        } else {
            $update->bindValue(':taskList', $taskList, SQLITE3_INTEGER);
        }

        $update->bindValue(':updatedBy', $userId, SQLITE3_INTEGER);
        $update->bindValue(':taskId', $targetTaskId, SQLITE3_INTEGER);

        $res = $update->execute();

        if ($res === false) {
            $message = "<p class='text-danger'>DB error: " . htmlspecialchars($db->lastErrorMsg()) . "</p>";
        } else {
            // SQLite UPDATE might not return a result set; no finalize needed.
            $message = "<p class='text-success'>Task updated</p>";
        }

        $update->close();
    }
}

/* ---------- Load task ---------- */
if ($role === 'Admin') {
    $stmt = $db->prepare("
        SELECT TaskID, TaskTitle, Description, Priority, Status, DueDate, TaskListID
        FROM Task
        WHERE TaskID = :taskId
        LIMIT 1
    ");
} else {
    $stmt = $db->prepare("
        SELECT TaskID, TaskTitle, Description, Priority, Status, DueDate, TaskListID
        FROM Task
        WHERE TaskID = :taskId
          AND LastUpdatedBy = :ownerId
        LIMIT 1
    ");
    $stmt->bindValue(':ownerId', $userId, SQLITE3_INTEGER);
}

$stmt->bindValue(':taskId', $targetTaskId, SQLITE3_INTEGER);
$resTask = $stmt->execute();
$task = $resTask->fetchArray(SQLITE3_ASSOC);
$resTask->finalize();
$stmt->close();

if (!$task) {
    echo "<p class='text-danger'>Task not found (or you do not have permission).</p>";
    exit();
}

/* ---------- Load task lists for dropdown ---------- */
$listsStmt = $db->prepare("
    SELECT TaskListID, ListTitle
    FROM TaskList
    WHERE LastUpdatedBy = :userId
      AND IsArchived = 0
    ORDER BY ListTitle
");
$listsStmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
$listsRes = $listsStmt->execute();

$currentListId = isset($task['TaskListID']) ? (int)$task['TaskListID'] : null;

$titleVal = htmlspecialchars($task['TaskTitle'] ?? '');
$descVal  = htmlspecialchars($task['Description'] ?? '');
$prioVal  = (string)($task['Priority'] ?? 'Low');
$statusVal = (string)($task['Status'] ?? 'Pending');
$dueVal   = $task['DueDate'] ?? '';
?>

<h1 class='page-title'>Update Task</h1>

<?php echo $message; ?>

<form class='addform' method='POST'>
    <input type='hidden' name='TaskID' value='<?php echo (int)$targetTaskId; ?>'>

    <label>Name</label>
    <input type='text' value='<?php echo htmlspecialchars($username); ?>' readonly>

    <label>Task Title</label>
    <input name='title' value='<?php echo $titleVal; ?>' required>

    <label>Description</label>
    <textarea name='desc' required><?php echo $descVal; ?></textarea>

    <label>Priority</label>
    <select name='priority'>
        <option value='Low' <?php echo ($prioVal === 'Low') ? 'selected' : ''; ?>>Low</option>
        <option value='Medium' <?php echo ($prioVal === 'Medium') ? 'selected' : ''; ?>>Medium</option>
        <option value='High' <?php echo ($prioVal === 'High') ? 'selected' : ''; ?>>High</option>
    </select>

    <label>Status</label>
    <select name='status'>
        <option value='Pending' <?php echo ($statusVal === 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value='Complete' <?php echo ($statusVal === 'Complete') ? 'selected' : ''; ?>>Complete</option>
        <option value='Cancelled' <?php echo ($statusVal === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
    </select>

    <label>Due Date (optional)</label>
    <input type='date' name='dueDate' value='<?php echo htmlspecialchars((string)$dueVal); ?>'>

    <label>Choose a Task List</label>
    <select name='TaskListID'>
        <option value=''>-- No task list --</option>
        <?php
        $hasLists = false;
        while ($lr = $listsRes->fetchArray(SQLITE3_ASSOC)) {
            $hasLists = true;
            $id = (int)$lr['TaskListID'];
            $t  = htmlspecialchars($lr['ListTitle'] ?? '');
            $sel = ($currentListId !== null && $id === $currentListId) ? 'selected' : '';
            echo "<option value='{$id}' {$sel}>{$t}</option>";
        }
        if (!$hasLists) {
            echo "<option value=''>No task lists available</option>";
        }
        $listsRes->finalize();
        $listsStmt->close();
        ?>
    </select>

    <button name='saveTask' type='submit'>Save</button>
</form>
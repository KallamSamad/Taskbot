<?php
require_once "db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
    exit("Not logged in");
}

$userId = (int)$_SESSION['userId'];
$message = "";

if (isset($_POST['createList'])) {

    $title    = trim($_POST['title'] ?? '');
    $desc     = trim($_POST['desc'] ?? '');
    $priority = $_POST['priority'] ?? 'Low';
    $status   = $_POST['status'] ?? 'Pending';
    $dueDate  = $_POST['dueDate'] ?? null;
    $tasks    = $_POST['tasks'] ?? [];

    if ($title === "") {
        $message = "Title required";
    } else {

        $stmt = $db->prepare("
            INSERT INTO TaskList
            (ListTitle, Description, DueDate, LastUpdatedBy, Priority, Status)
            VALUES (:title, :desc, :due, :uid, :priority, :status)
        ");

        $stmt->bindValue(":title", $title, SQLITE3_TEXT);
        $stmt->bindValue(":desc", $desc, SQLITE3_TEXT);
        $stmt->bindValue(":due", $dueDate ?: null, SQLITE3_TEXT);
        $stmt->bindValue(":uid", $userId, SQLITE3_INTEGER);
        $stmt->bindValue(":priority", $priority, SQLITE3_TEXT);
        $stmt->bindValue(":status", $status, SQLITE3_TEXT);

        $stmt->execute();
        $listId = $db->lastInsertRowID();
        $stmt->close();

        if (!empty($tasks)) {

            $update = $db->prepare("
                UPDATE Task
                SET TaskListID = :listId
                WHERE TaskID = :taskId
                  AND LastUpdatedBy = :uid
            ");

            foreach ($tasks as $taskId) {
                $update->bindValue(":listId", $listId, SQLITE3_INTEGER);
                $update->bindValue(":taskId", (int)$taskId, SQLITE3_INTEGER);
                $update->bindValue(":uid", $userId, SQLITE3_INTEGER);
                $update->execute();
            }

            $update->close();
        }

        $message = "Task list created";
    }
}

$taskQuery = $db->prepare("
    SELECT TaskID, TaskTitle
    FROM Task
    WHERE LastUpdatedBy = :uid
      AND TaskListID IS NULL
      AND IsArchived = 0
    ORDER BY TaskTitle
");

$taskQuery->bindValue(":uid", $userId, SQLITE3_INTEGER);
$tasks = $taskQuery->execute();
?>

<?= $message ?>

<form method="POST" class="addform">

<label>Task List Title</label>
<input name="title" required>

<label>Description</label>
<textarea name="desc"></textarea>

<label>Priority</label>
<select name="priority">
  <option>Low</option>
  <option>Medium</option>
  <option>High</option>
</select>

<label>Status</label>
<select name="status">
  <option>Pending</option>
  <option>Complete</option>
  <option>Cancelled</option>
</select>

<label>Due Date</label>
<input type="date" name="dueDate">

<h3>Select Tasks for this List</h3>

<?php
$hasTasks = false;

while ($row = $tasks->fetchArray(SQLITE3_ASSOC)) {

    $hasTasks = true;

    echo "
    <label class='task-checkbox'>
        <input type='checkbox' name='tasks[]' value='{$row['TaskID']}'>
        " . htmlspecialchars($row['TaskTitle']) . "
    </label>
    ";
}

if (!$hasTasks) {
    echo "<p>No available tasks</p>";
}

$tasks->finalize();
$taskQuery->close();
?>

<button name="createList">Create Task List</button>

</form>
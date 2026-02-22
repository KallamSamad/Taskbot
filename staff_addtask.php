<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userId'])) {
    echo "Not logged in";
    exit();
}

$userId   = (int)$_SESSION['userId'];
$username = (string)($_SESSION['username'] ?? '');

$message = "";

if (isset($_POST['addtask'])) {

    $title    = trim((string)($_POST['title'] ?? ''));
    $desc     = trim((string)($_POST['desc'] ?? ''));
    $priority = (string)($_POST['priority'] ?? 'Low');
    $status   = (string)($_POST['status'] ?? 'Pending');
    $taskList = $_POST['TaskListID'] ?? null;
    $dueDate  = $_POST['dueDate'] ?? '';

    if ($taskList === "") {
        $taskList = null;
    } else if ($taskList !== null) {
        $taskList = (int)$taskList;
    }

    if ($dueDate === "") {
        $dueDate = null;
    }

    if ($title === "" || $desc === "") {
        $message = "<p class='text-danger'>Title and description are required.</p>";
    } else {
        $insert = $db->prepare("
            INSERT INTO Task (
                TaskTitle,
                Description,
                LastUpdatedBy,
                Priority,
                Status,
                DueDate,
                IsArchived,
                TaskListID
            )
            VALUES (
                :title,
                :desc,
                :userId,
                :priority,
                :status,
                :dueDate,
                0,
                :taskList
            )
        ");

        $insert->bindValue(":title", $title, SQLITE3_TEXT);
        $insert->bindValue(":desc", $desc, SQLITE3_TEXT);
        $insert->bindValue(":userId", $userId, SQLITE3_INTEGER);
        $insert->bindValue(":priority", $priority, SQLITE3_TEXT);
        $insert->bindValue(":status", $status, SQLITE3_TEXT);

        if ($dueDate === null) {
            $insert->bindValue(":dueDate", null, SQLITE3_NULL);
        } else {
            $insert->bindValue(":dueDate", $dueDate, SQLITE3_TEXT);
        }

        if ($taskList === null) {
            $insert->bindValue(":taskList", null, SQLITE3_NULL);
        } else {
            $insert->bindValue(":taskList", $taskList, SQLITE3_INTEGER);
        }

        $res = $insert->execute();

        if ($res === false) {
            $message = "<p class='text-danger'>DB error: " . htmlspecialchars($db->lastErrorMsg()) . "</p>";
        } else {
            $res->finalize();
            $message = "<p class='text-success'>Task added</p>";
        }

        $insert->close();
    }
}

$stmt = $db->prepare("
    SELECT TaskListID, ListTitle
    FROM TaskList
    WHERE LastUpdatedBy = :userId
      AND IsArchived = 0
    ORDER BY ListTitle
");
$stmt->bindValue(":userId", $userId, SQLITE3_INTEGER);
$result = $stmt->execute();
?>

<?php echo $message; ?>

<form class="addform" method="POST">

    <label>Name</label>
    <input type="text" value="<?php echo htmlspecialchars($username); ?>" readonly>

    <label>Task Title</label>
    <input name="title" placeholder="Add Title here..." required>

    <label>Description</label>
    <textarea name="desc" placeholder="Add Description here..." required></textarea>

    <label>Priority</label>
    <select name="priority">
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
    </select>

    <label>Status</label>
    <select name="status">
        <option value="Pending">Pending</option>
        <option value="Complete">Complete</option>
        <option value="Cancelled">Cancelled</option>
    </select>

    <label>Due Date (optional)</label>
    <input type="date" name="dueDate">

    <label>Choose a Task List</label>
    <select name="TaskListID">
        <option value="">-- No task list --</option>

        <?php
        $hasLists = false;

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $hasLists = true;
            $id = (int)$row['TaskListID'];
            $t  = htmlspecialchars($row['ListTitle'] ?? '');
            echo "<option value='{$id}'>{$t}</option>";
        }

        if (!$hasLists) {
            echo "<option value=''>No task lists available</option>";
        }

        $result->finalize();
        $stmt->close();
        ?>
    </select>

    <button name="addtask" type="submit">Add Task</button>

</form>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') {
    header("Location: index.php?page=home");
    exit();
}

if (!isset($_SESSION['userId'])) {
    echo "<p>Session missing userId. Log out and log in again.</p>";
    exit();
}

$db = new SQLite3("C:/xampp/htdocs/TaskBot/database.db");
$db->exec("PRAGMA foreign_keys = ON;");

$userId = (int)$_SESSION['userId'];

$records_per_page = 4;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

$countStmt = $db->prepare("SELECT COUNT(*) AS total FROM Task WHERE LastUpdatedBy = :uid");
$countStmt->bindValue(":uid", $userId, SQLITE3_INTEGER);
$countRes = $countStmt->execute();
$countRow = $countRes->fetchArray(SQLITE3_ASSOC);

$countRes->finalize();
$countStmt->close();

$total_records = (int)($countRow['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_records / $records_per_page));

$stmt = $db->prepare("
    SELECT
        U.DisplayName,
        T.TaskID,
        T.TaskTitle,
        T.Description,
        T.CreateDate,
        T.DueDate,
        T.UpdatedAt,
        T.Priority,
        T.Status
    FROM Task T
    INNER JOIN Users U ON U.UserID = T.LastUpdatedBy
    WHERE T.LastUpdatedBy = :uid
    ORDER BY T.DueDate IS NULL, T.DueDate ASC, T.TaskID DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(":uid", $userId, SQLITE3_INTEGER);
$stmt->bindValue(":limit", $records_per_page, SQLITE3_INTEGER);
$stmt->bindValue(":offset", $offset, SQLITE3_INTEGER);

$result = $stmt->execute();

echo "<h1 class='page-title'>My Tasks</h1>";

echo "<div class='table-wrap'>";
echo "<table class='tasks-table'>";

echo "
<thead>
<tr>
  <th>Name</th>
  <th>Task Title</th>
  <th>Description</th>
  <th>Created</th>
  <th>Due</th>
  <th>Updated</th>
  <th>Priority</th>
  <th>Status</th>
  <th>Update</th>
  <th>Delete</th>
</tr>
</thead>
<tbody>
";

$hasRows = false;
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $hasRows = true;

    $name = htmlspecialchars($row['DisplayName'] ?? '');
    $taskId = (int)($row['TaskID'] ?? 0);

    $title = htmlspecialchars($row['TaskTitle'] ?? '');
    $desc = htmlspecialchars($row['Description'] ?? '');
    $created = htmlspecialchars($row['CreateDate'] ?? '');
    $due = htmlspecialchars($row['DueDate'] ?? '');
    $updated = htmlspecialchars($row['UpdatedAt'] ?? '');

    $priorityRaw = trim((string)($row['Priority'] ?? ''));
    $priority = htmlspecialchars($priorityRaw);
    $priorityClass = 'priority-' . strtolower(preg_replace('/\s+/', '-', $priorityRaw));

    $statusRaw = trim((string)($row['Status'] ?? ''));
    $status = htmlspecialchars($statusRaw);
    $statusClass = 'status-' . strtolower(preg_replace('/\s+/', '-', $statusRaw));

    echo "<tr>
        <td>{$name}</td>
        <td>{$title}</td>
        <td class='desc'>{$desc}</td>
        <td>{$created}</td>
        <td>{$due}</td>
        <td>{$updated}</td>
        <td class='{$priorityClass}'>{$priority}</td>
        <td class='{$statusClass}'>{$status}</td>

        <td>
          <form method='POST' action='updatetask.php'>
            <input type='hidden' name='TaskID' value='{$taskId}'>
            <button class='btn' type='submit'>Update</button>
          </form>
        </td>

        <td>
          <form method='POST' action='deletetask.php' onsubmit=\"return confirm('Delete this task?');\">
            <input type='hidden' name='TaskID' value='{$taskId}'>
            <button class='btn' type='submit'>Delete</button>
          </form>
        </td>
    </tr>";
}

if (!$hasRows) {
    echo "<tr><td colspan='10'>No tasks found.</td></tr>";
}

$result->finalize();
$stmt->close();

echo "</tbody></table>";
echo "</div>";

echo "<div class='pagination'>";

if ($current_page > 1) {
    $prev = $current_page - 1;
    echo "<a href='index.php?page=tasks&p={$prev}'>Previous</a>";
}

for ($i = 1; $i <= $total_pages; $i++) {
    if ($i === $current_page) {
        echo "<strong>{$i}</strong>";
    } else {
        echo "<a href='index.php?page=tasks&p={$i}'>{$i}</a>";
    }
}

if ($current_page < $total_pages) {
    $next = $current_page + 1;
    echo "<a href='index.php?page=tasks&p={$next}'>Next</a>";
}

echo "</div>";
?>
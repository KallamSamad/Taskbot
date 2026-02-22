<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: index.php");
    exit();
}

 
$db->exec("PRAGMA foreign_keys = ON;");

$records_per_page = 4;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

$countRes = $db->query("SELECT COUNT(*) AS total FROM Task WHERE IsArchived = 0");
$countRow = $countRes->fetchArray(SQLITE3_ASSOC);
$total_records = (int)$countRow['total'];
$total_pages = max(1, ceil($total_records / $records_per_page));

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
    LEFT JOIN Users U ON U.UserID = T.LastUpdatedBy
    WHERE T.IsArchived = 0
    ORDER BY T.UpdatedAt DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(":limit", $records_per_page, SQLITE3_INTEGER);
$stmt->bindValue(":offset", $offset, SQLITE3_INTEGER);

$result = $stmt->execute();

echo "<h1 class='page-title'>All Tasks</h1>";
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

    $name = htmlspecialchars($row['DisplayName'] ?? 'Unknown');
    $taskId = (int)$row['TaskID'];

    $title = htmlspecialchars($row['TaskTitle']);
    $desc = htmlspecialchars($row['Description']);
    $created = htmlspecialchars($row['CreateDate']);
    $due = htmlspecialchars($row['DueDate']);
    $updated = htmlspecialchars($row['UpdatedAt']);

    $priorityRaw = trim((string)$row['Priority']);
    $priorityClass = 'priority-' . strtolower($priorityRaw);

    $statusRaw = trim((string)$row['Status']);
    $statusClass = 'status-' . strtolower($statusRaw);

    echo "<tr>
        <td>{$name}</td>
        <td>{$title}</td>
        <td class='desc'>{$desc}</td>
        <td>{$created}</td>
        <td>{$due}</td>
        <td>{$updated}</td>
        <td class='{$priorityClass}'>{$priorityRaw}</td>
        <td class='{$statusClass}'>{$statusRaw}</td>

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

echo "</tbody></table>";
echo "</div>";

echo "<div class='pagination'>";

if ($current_page > 1) {
    $prev = $current_page - 1;
    echo "<a href='index.php?page=alltasks&p={$prev}'>Previous</a>";
}

for ($i = 1; $i <= $total_pages; $i++) {
    if ($i === $current_page) {
        echo "<strong>{$i}</strong>";
    } else {
        echo "<a href='index.php?page=alltasks&p={$i}'>{$i}</a>";
    }
}

if ($current_page < $total_pages) {
    $next = $current_page + 1;
    echo "<a href='index.php?page=alltasks&p={$next}'>Next</a>";
}

echo "</div>";

$result->finalize();
$stmt->close();
 
?>
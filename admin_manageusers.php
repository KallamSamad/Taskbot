<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: index.php');
    exit();
}

$db->exec('PRAGMA foreign_keys = ON;');

$records_per_page = 4;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) $current_page = 1;

$offset = ($current_page - 1) * $records_per_page;

$countRes = $db->query('SELECT COUNT(*) AS total FROM Users');
$countRow = $countRes->fetchArray(SQLITE3_ASSOC);
$total_records = (int)($countRow['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_records / $records_per_page));

$stmt = $db->prepare("
    SELECT
        U.UserID,
        U.DisplayName,
        U.FirstName,
        U.LastName,
        U.Email,
        U.Phone,
        U.IsActive,
        R.RoleType,
        U.LastLoginAt
    FROM Users U
    INNER JOIN Role R ON R.RoleID = U.RoleID
    ORDER BY U.UserID ASC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $records_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

$result = $stmt->execute();

echo "<h1 class='page-title'>Manage Users</h1>";
echo "<div class='table-wrap'>";
echo "<table class='tasks-table'>";

echo "
<thead>
<tr>
  <th>ID</th>
  <th>Display Name</th>
  <th>First</th>
  <th>Last</th>
  <th>Email</th>
  <th>Phone</th>
  <th>Role</th>
  <th>Active</th>
  <th>Last Login</th>
  <th>Update</th>
  <th>Delete</th>
</tr>
</thead>
<tbody>
";

$hasRows = false;

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $hasRows = true;

    $userId  = (int)($row['UserID'] ?? 0);
    $display = htmlspecialchars($row['DisplayName'] ?? '');
    $first   = htmlspecialchars($row['FirstName'] ?? '');
    $last    = htmlspecialchars($row['LastName'] ?? '');
    $email   = htmlspecialchars($row['Email'] ?? '');
    $phone   = htmlspecialchars($row['Phone'] ?? '');
    $role    = htmlspecialchars($row['RoleType'] ?? '');
    $login   = htmlspecialchars($row['LastLoginAt'] ?? '');
    $active  = !empty($row['IsActive']) ? 'Yes' : 'No';

    echo "
<tr>
    <td>{$userId}</td>
    <td>{$display}</td>
    <td>{$first}</td>
    <td>{$last}</td>
    <td>{$email}</td>
    <td>{$phone}</td>
    <td>{$role}</td>
    <td>{$active}</td>
    <td>{$login}</td>

    <td>
        <form method='POST' action='index.php?page=updateuser'>
            <input type='hidden' name='UserID' value='{$userId}'>
            <button class='btn' type='submit'>Update</button>
        </form>
    </td>

    <td>
        <form method='POST' action='deleteuser.php' onsubmit='return confirm(\"Delete this user?\");'>
            <input type='hidden' name='UserID' value='{$userId}'>
            <button class='btn' type='submit'>Delete</button>
        </form>
    </td>
</tr>
";
}

if (!$hasRows) {
    echo "<tr><td colspan='11'>No users found.</td></tr>";
}

echo "</tbody></table>";
echo "</div>";

echo "<div class='pagination'>";

if ($current_page > 1) {
    $prev = $current_page - 1;
    echo "<a href='index.php?page=manageusers&p={$prev}'>Previous</a>";
}

for ($i = 1; $i <= $total_pages; $i++) {
    if ($i === $current_page) {
        echo "<strong>{$i}</strong>";
    } else {
        echo "<a href='index.php?page=manageusers&p={$i}'>{$i}</a>";
    }
}

if ($current_page < $total_pages) {
    $next = $current_page + 1;
    echo "<a href='index.php?page=manageusers&p={$next}'>Next</a>";
}

echo "</div>";

$result->finalize();
$stmt->close();
?>
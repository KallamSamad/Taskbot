<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    echo 'Not authorised';
    exit();
}

require_once 'db.php';
$db->exec('PRAGMA foreign_keys = ON;');

if (!isset($_POST['UserID']) && !isset($_GET['UserID'])) {
    echo "<p class='text-danger'>No user selected.</p>";
    exit();
}

$targetUserId = isset($_POST['UserID']) ? (int)$_POST['UserID'] : (int)$_GET['UserID'];
$adminName = (string)($_SESSION['username'] ?? '');
$message = '';

if (isset($_POST['save'])) {

    $display = trim((string)($_POST['DisplayName'] ?? ''));
    $first   = trim((string)($_POST['FirstName'] ?? ''));
    $last    = trim((string)($_POST['LastName'] ?? ''));
    $email   = trim((string)($_POST['Email'] ?? ''));
    $phone   = trim((string)($_POST['Phone'] ?? ''));
    $roleId  = (int)($_POST['RoleID'] ?? 0);
    $active  = (int)($_POST['IsActive'] ?? 1);

    if ($display === '' || $first === '' || $last === '' || $email === '') {
        $message = "<p class='text-danger'>Display name, first name, last name and email are required.</p>";
    } else {
        $stmt = $db->prepare("
            UPDATE Users
            SET DisplayName = :display,
                FirstName   = :first,
                LastName    = :last,
                Email       = :email,
                Phone       = :phone,
                RoleID      = :role,
                IsActive    = :active
            WHERE UserID = :id
        ");

        $stmt->bindValue(':display', $display, SQLITE3_TEXT);
        $stmt->bindValue(':first',   $first,   SQLITE3_TEXT);
        $stmt->bindValue(':last',    $last,    SQLITE3_TEXT);
        $stmt->bindValue(':email',   $email,   SQLITE3_TEXT);
        $stmt->bindValue(':phone',   $phone,   SQLITE3_TEXT);
        $stmt->bindValue(':role',    $roleId,  SQLITE3_INTEGER);
        $stmt->bindValue(':active',  $active,  SQLITE3_INTEGER);
        $stmt->bindValue(':id',      $targetUserId, SQLITE3_INTEGER);

        $res = $stmt->execute();

        if ($res === false) {
            $message = "<p class='text-danger'>DB error: " . htmlspecialchars($db->lastErrorMsg()) . "</p>";
        } else {
            $res->finalize();
            $message = "<p class='text-success'>User updated</p>";
        }

        $stmt->close();
    }
}

$stmt = $db->prepare("
    SELECT
        U.*,
        R.RoleType,
        C.Username
    FROM Users U
    INNER JOIN Role R ON R.RoleID = U.RoleID
    INNER JOIN Credentials C ON C.UserID = U.UserID
    WHERE U.UserID = :id
    LIMIT 1
");
$stmt->bindValue(':id', $targetUserId, SQLITE3_INTEGER);
$resUser = $stmt->execute();
$user = $resUser->fetchArray(SQLITE3_ASSOC);
$resUser->finalize();
$stmt->close();

if (!$user) {
    echo "<p class='text-danger'>User not found.</p>";
    exit();
}

$roles = $db->query('SELECT RoleID, RoleType FROM Role');
?>

<h1 class="page-title">Update User</h1>

<?php echo $message; ?>

<form class="addform" method="POST" >
    <input type="hidden" name="UserID" value="<?php echo (int)$targetUserId; ?>">

    <label>Admin</label>
    <input type="text" value="<?php echo htmlspecialchars($adminName); ?>" readonly>

    <label>Username</label>
    <input type="text" value="<?php echo htmlspecialchars($user['Username'] ?? ''); ?>" readonly>

    <label>User ID</label>
    <input type="text" value="<?php echo (int)$targetUserId; ?>" readonly>

    <label>Display Name</label>
    <input name="DisplayName" value="<?php echo htmlspecialchars($user['DisplayName'] ?? ''); ?>" required>

    <label>First Name</label>
    <input name="FirstName" value="<?php echo htmlspecialchars($user['FirstName'] ?? ''); ?>" required>

    <label>Last Name</label>
    <input name="LastName" value="<?php echo htmlspecialchars($user['LastName'] ?? ''); ?>" required>

    <label>Email</label>
    <input type="email" name="Email" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>

    <label>Phone</label>
    <input name="Phone" value="<?php echo htmlspecialchars($user['Phone'] ?? ''); ?>">

    <label>Role</label>
    <select name="RoleID">
        <?php while ($r = $roles->fetchArray(SQLITE3_ASSOC)): ?>
            <option value="<?php echo (int)$r['RoleID']; ?>"
                <?php echo ((int)$r['RoleID'] === (int)$user['RoleID']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($r['RoleType']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Active</label>
    <select name="IsActive">
        <option value="1" <?php echo ((int)$user['IsActive'] === 1) ? 'selected' : ''; ?>>Yes</option>
        <option value="0" <?php echo ((int)$user['IsActive'] === 0) ? 'selected' : ''; ?>>No</option>
    </select>

    <button name="save" type="submit">Save</button>
</form>
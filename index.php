<?php
session_start();
require_once "db.php";

/* =========================
   FLASH MESSAGES
========================= */
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

/* =========================
   LOGIN HANDLER
========================= */
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['LoginButton'])) {

    $usernameIn = $_POST['username'] ?? "";
    $password   = $_POST['password'] ?? "";

    $stmt = $db->prepare("
        SELECT Username, HashedPassword
        FROM Credentials
        WHERE Username = :username
        LIMIT 1
    ");

    $stmt->bindValue(":username", $usernameIn, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    $result->finalize();
    $stmt->close();

    if (!$row) {
        $message = "Incorrect Username or Password";
    } else {

        if (password_verify($password, $row["HashedPassword"])) {

            $_SESSION['username'] = $row['Username'];

            // fetch role + user id
            $stmt2 = $db->prepare("
                SELECT C.UserID, R.RoleType
                FROM Credentials AS C
                INNER JOIN Users AS U ON C.UserID = U.UserID
                INNER JOIN Role AS R ON U.RoleID = R.RoleID
                WHERE C.Username = :username
                LIMIT 1
            ");

            $stmt2->bindValue(":username", $_SESSION['username'], SQLITE3_TEXT);
            $result2 = $stmt2->execute();
            $row2 = $result2->fetchArray(SQLITE3_ASSOC);

            $result2->finalize();
            $stmt2->close();

            $_SESSION['role'] = $row2['RoleType'] ?? 'Staff';
            $_SESSION['userId'] = isset($row2['UserID']) ? (int)$row2['UserID'] : null;

            header("Location: index.php?page=home");
            exit;

        } else {
            $message = "Your password is incorrect!";
        }
    }
}

/* =========================
   ENSURE ROLE EXISTS
========================= */
if (isset($_SESSION['username']) && !isset($_SESSION['role'])) {

    $stmt = $db->prepare("
        SELECT C.UserID, R.RoleType
        FROM Credentials AS C
        INNER JOIN Users AS U ON C.UserID = U.UserID
        INNER JOIN Role AS R ON U.RoleID = R.RoleID
        WHERE C.Username = :username
        LIMIT 1
    ");

    $stmt->bindValue(":username", $_SESSION['username'], SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    $result->finalize();
    $stmt->close();

    $_SESSION['role'] = $row['RoleType'] ?? 'Staff';
    $_SESSION['userId'] = isset($row['UserID']) ? (int)$row['UserID'] : null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="/TaskBot/a11y.css">
    <script src="/TaskBot/a11y.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">

    <title>TaskBot</title>

    <!-- A11Y INIT -->
    <script>
    (function () {
      const keys = ["a11y-dark", "a11y-large-text", "a11y-contrast"];
      const root = document.documentElement;

      for (const k of keys) {
        const on = localStorage.getItem(k) === "true";
        root.classList.toggle(k, on);
      }
    })();
    </script>

</head>

<body>

<!-- FLASH MESSAGES -->
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger m-2">
    <?= htmlspecialchars($flash_error) ?>
  </div>
<?php endif; ?>

<?php if (!empty($flash_success)): ?>
  <div class="alert alert-success m-2">
    <?= htmlspecialchars($flash_success) ?>
  </div>
<?php endif; ?>

<?php if (!empty($message)): ?>
  <p class="text-danger m-2">
    <?= htmlspecialchars($message) ?>
  </p>
<?php endif; ?>

<div class="header">

    <!-- LEFT PANEL -->
    <div class="left"></div>

    <!-- MAIN CONTENT -->
    <div class="middle">

        <div class="quadlayer">

            <div class="layer1">
                <h1><b>Taskbot</b></h1>
                <img class="logo" src="Assets/Images/logo.png" alt="TaskBot logo">
            </div>

            <?php require_once 'nav.php'; ?>

            <?php if (!isset($_SESSION['username'])): ?>

                                 <div class="layer2">
                    <h2 style="padding-top:30px;">What is Taskbot?</h2>
                    <p class="paragraph">Taskbot is a very intuitive and structured way of organising tasks. Be it day-to-day "to do lists" to planning a project for your work. If you want an effiecnt way of organising your tasks, Taskbot is the app!</p>
                </div>

                <div class="layer3">

                    <div class="dead">
                        <img src="Assets/Images/deadline.jpg" alt="An image of a calendar" height="50px">
                        <h2>Deadlines and Status</h2>
                    </div>
                    <p class="paragraph">You can change the <b>status</b> of a task from <b>pending</b> to <b>completed</b>. You can also set deadlines to see if the task is done.</p>

                    <div class="group">
                        <img src="Assets/Images/team.png" alt="An image of a team working" height="50px">
                        <h2>Grouping Tasks</h2>
                    </div>
                    <p class="paragraph">You can <b>group</b> similar tasks into a bigger task list to share with users who you are working with. <i>Great</i> for <b>teamwork</b>.</p>

                    <div class="group">
                        <img src="Assets/Images/dart.jpg" alt="An image of a dartboard " height="50px">
                        <h2>Priority</h2>
                    </div>
                    <p class="paragraph">You can <b>quantify</b> which tasks are more important than others, allowing seamless priotitisation.</p>

                </div>

                <div class="layer4"></div>

            <?php else: ?>

                <?php
                $page = $_GET['page'] ?? 'home';
                $role = $_SESSION['role'] ?? 'Staff';

                if ($role === 'Admin') {
                    switch ($page) {
                        case 'updateuser': require 'updateuser.php'; break;
                        case 'updatetask': require 'updatetask.php'; break;
                        case 'updatetasklist': require 'updatetasklist.php'; break;
                        case 'alltasklists': require 'admin_alltasklists.php'; break;
                        case 'alltasks': require 'admin_alltasks.php'; break;
                        case 'manageusers': require 'admin_manageusers.php'; break;
                        case 'addtask': require 'admin_addtask.php'; break;
                        case 'addtasklist': require 'admin_addtasklist.php'; break;
                        default: require 'admin_home.php'; break;
                    }
                } else {
                    switch ($page) {
                        case 'updatetask': require 'updatetask.php'; break;
                        case 'updatetasklist': require 'updatetasklist.php'; break;
                        case 'tasks': require 'stafftasks.php'; break;
                        case 'lists': require 'stafflists.php'; break;
                        case 'addtask': require 'staff_addtask.php'; break;
                        case 'addtasklist': require 'staff_addtasklist.php'; break;
                        default: require 'staff.php'; break;
                    }
                }
                ?>

            <?php endif; ?>

        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right">

        <?php if (!isset($_SESSION['username'])): ?>

            <h1 class="signinhead">Sign In</h1>

            <div class="signin">
                <form method="POST">

                    <label>Username</label>
                    <input name="username" type="text" required>

                    <label>Password</label>
                    <input name="password" type="password"
                           pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                           required>

                    <input name="LoginButton" class="submitbtn" type="submit" value="Submit">

                </form>
            </div>

        <?php else: ?>

            <?php require 'dashboard.php'; ?>

        <?php endif; ?>

    </div>

</div>

<?php require_once "footer.php"; ?>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
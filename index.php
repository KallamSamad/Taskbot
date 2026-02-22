<?php
session_start();

$message = '';
$flash = '';

require_once "db.php";
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
        $message = "No user found";
    } else {
        if (password_verify($password, $row["HashedPassword"])) {
            $_SESSION['username'] = $row['Username'];

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

            $db->close();
            header("Location: index.php?page=home");
            exit;
        } else {
            $message = "Your password is incorrect!";
        }
    }
}

if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if (isset($_SESSION['username']) && !isset($_SESSION['role'])) {
    $username = $_SESSION['username'];

    $stmt = $db->prepare("
        SELECT C.UserID, R.RoleType
        FROM Credentials AS C
        INNER JOIN Users AS U ON C.UserID = U.UserID
        INNER JOIN Role AS R ON U.RoleID = R.RoleID
        WHERE C.Username = :username
        LIMIT 1
    ");
    $stmt->bindValue(":username", $username, SQLITE3_TEXT);

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="/TaskBot/a11y.css">
<script src="/TaskBot/a11y.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <title>TaskBot</title>
</head>
<body>

<?php if (!empty($message)) echo "<p class='text-danger' style='margin:10px;'>" . htmlspecialchars($message) . "</p>"; ?>
<?php if (!empty($flash))   echo "<p class='text-success' style='margin:10px;'>" . htmlspecialchars($flash) . "</p>"; ?>

<div class="header">

    <div class="left">
        <img src="Assets/Images/waterfall.webp" alt="Picture of a waterfall" class="sr-only">
    </div>

    <div class="middle">
        <div class="quadlayer">

            <div class="layer1">
                <h1 style="text-align:center"><b>Taskbot</b></h1>
                <img class="logo" src="Assets/Images/logo.png" alt="logo of a green robot with a check marked">
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
                        case 'alltasks':    require 'admin_alltasks.php'; break;
                        case 'manageusers': require 'admin_manageusers.php'; break;
                        case 'addtask': require 'admin_addtask.php'; break;
                        case 'addtasklist': require 'admin_addtasklist.php'; break;
                        default:            require 'admin_home.php'; break;
                    }
                } else {
                    switch ($page) {
                        case 'updatetask': require 'updatetask.php'; break;
                        case 'updatetasklist': require 'updatetasklist.php'; break;
                        case 'tasks':   require 'stafftasks.php'; break;
                        case 'lists':   require 'stafflists.php'; break;
                        case 'addtask': require 'staff_addtask.php'; break;
                        case 'addtasklist': require 'staff_addtasklist.php'; break;
                        default:        require 'staff.php'; break;
                    }
                }
                ?>

            <?php endif; ?>

        </div>
    </div>

    <div class="right">
        <?php if (!isset($_SESSION['username'])): ?>

            <h1 class="signinhead">Sign In </h1>
            <div class="signin">
                <form method="POST">
                    <label for="username">Username</label>
                    <input name="username" type="text" required placeholder="Enter Username Here">

                    <label for="password">Password</label>
                    <input name="password" type="password"
                           pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                           required
                           oninvalid="this.setCustomValidity('Password must be at least 8 characters and include an uppercase letter, a number, and a special character.')"
                           oninput="this.setCustomValidity('')"
                           placeholder="Enter password here">
                    <input name="LoginButton" class="submitbtn" type="submit" value="Submit">
                </form>
            </div>

            <p class="paragraph">Not got an account? <a style="color:maroon" href="signup.php">Sign up</a></p>
            <p class="paragraph"><a style="color:maroon;" href="forgotpassword.php">Forgot Passsword?</a></p>

        <?php else: ?>

            <?php require 'dashboard.php'; ?>

        <?php endif; ?>
    </div>

</div>

<div class="footer">By Kallam Samad 2026</div>
 
</body>
</html>
 
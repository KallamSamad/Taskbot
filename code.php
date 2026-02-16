<?php
session_start();
$message = '';
$db = new SQLite3("C:/xampp/htdocs/TaskBot/database.db");
$db->exec("PRAGMA foreign_keys = ON;");
require_once "function.php";

$id = $_SESSION['id'] ?? null;

if (!$id) {
    $message = "Session expired. Go back and enter your email again.";
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $enteredCode = trim($_POST['code'] ?? '');
        $pw = $_POST['pw'] ?? '';
        $conf = $_POST['confpw'] ?? '';

        if ($pw !== $conf) {
            $message = "Passwords do not match.";
        } else {
            $stmt = $db->prepare("SELECT ResetToken FROM Credentials WHERE UserID = :id");
            $stmt->bindValue(":id", $id, SQLITE3_INTEGER);
            $res = $stmt->execute();
            $row = $res->fetchArray(SQLITE3_ASSOC);

            if (!$row || $row['ResetToken'] !== $enteredCode) {
                $message = "Invalid code.";
            } else {
                updatePW($db, $id, $pw);
                $_SESSION['flash'] = "Password updated successfully";
                header("Location: index.php");
                exit();
            }
        }

    } else {
        $generatedCode = code($db, null, $id);
        echo $generatedCode;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <title>TaskBot</title>
</head>
<body>
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
                   <?php
    require_once 'nav.php';
    ?>
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


                <div class="layer4">

                </div>
            </div>

        </div>
        
        
        <div class="right">
            <h1 class="signinhead">Sign In </h1>
        <div class="signin">
        <?php if (!empty($message)) echo "<p style='color:maroon;'>$message</p>"; ?>

        <form class="verify" method="POST">

            <label for="code">Code</label>
            <input name="code" type="text" inputmode="numeric" pattern="^\d{8}$"
                maxlength="8" minlength="8" required placeholder="Enter 8-digit code">

            <label>Password</label>
            <input name="pw" class="pw" type="password"
                pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                required oninput="checkPasswords()" placeholder="Enter password here">

            <label>Confirm Password</label>
            <input name="confpw" class="confpw" type="password"
                pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                required oninput="checkPasswords()" placeholder="Confirm password here">

            <p id="pwError" style="color:red; display:none;">Passwords must match</p>

            <input name="LoginButton" class="submitbtn" type="submit" value="Submit">

        </form>
        </div>
        <p class="paragraph">Not got an account? <a style="color:maroon" href="signup.php">Sign up</a></p>
        </div>
        </div>
<div class="footer">By Kallam Samad 2026 </div>
</body>
</html>
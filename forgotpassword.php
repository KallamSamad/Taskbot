<?php
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';

    $id = $db->prepare("SELECT UserID FROM Users WHERE Email = :email LIMIT 1;");
    $id->bindValue(":email", $email, SQLITE3_TEXT);

    $result = $id->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    $result->finalize();
    $id->close();

    if ($row) {
        $_SESSION['id'] = (int)$row['UserID'];
        $db->close();
        header("Location: code.php");
        exit();
    } else {
        $message = "Email Not Found";
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
            <?php if (!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
            <form class="verify" method="POST">
            <label for="email">Email</label>
            <input name="email" type="email" required placeholder="Enter email Here">

 
            <input name="LoginButton" class="submitbtn" type="submit" value="Submit">
        </form>
        </div>
        <p class="paragraph">Not got an account? <a style="color:maroon" href="signup.php">Sign up</a></p>
        </div>
        </div>
<div class="footer">By Kallam Samad 2026 </div>
</body>
</html>
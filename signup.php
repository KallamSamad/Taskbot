<?php 
session_start();
$db = new SQLite3("database.db");
$db->exec("PRAGMA foreign_keys = ON;");

if($db){
    echo "Connection successful";
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
                <img class="waterfall">
        </div>
        
        <div class="middle">
            <div class="quadlayer">
                <div class="layer1">
                    <h1 style="text-align:center"><b>Taskbot</b></h1>
                    <img class="logo" src="Assets/Images/logo.png" alt="logo of a green robot with a check marked">
                </div>
                   <?php
    require_once 'nav.php';
    require_once 'function.php';
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
        
<?php
if (isset($_POST['adduser'])) {

    if ($_POST['pw'] !== $_POST['confpw']) {
        echo "<p style='color:red;'>Passwords must match</p>";
    } else {

        $userID = addUser(
            $db,
            (int)$_POST['role'],
            $_POST['display'],
            $_POST['fname'],
            $_POST['mname'],
            $_POST['lname'],
            $_POST['email'],
            $_POST['phone'],
            1
        );

        addCredentials(
            $db,
            $userID,
            $_POST['username'],
            $_POST['pw']
        );

        header("Location: index.php");
        exit;
    }
}
?>

 
        
        <div class="right">
            <h1 class="signinhead">Sign Up </h1>
            <div class="signin">
            <form method="POST" >
            <input type="hidden" name="role" value="2">
            <label>Username:</label>
            <input name="username" type="text" required placeholder="Enter Username Here">
            <label>Display Name:</label>
            <input name="display" type="text" required placeholder="Enter Display Name Here">
            <label>First name</label>
            <input name="fname" type="text" required placeholder="Enter First Name Here">
            <label>Middle name:</label>
            <input name="mname" type="text"  placeholder="Enter Middle Name Here">
            <label>Last name</label>
            <input name="lname" type="text" required placeholder="Enter Last Name Here">

            <label>Email:</label>
            <input name="email" type="email" required placeholder="Enter Email Here">
            
            <label>Phone</label>
            <input name="phone"type="phone" required placeholder="Enter Phone Name Here">
            
            <label>Password</label>
            <input name="pw" class="pw"type="password" pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$" required  oninvalid="this.setCustomValidity('Password must be at least 8 characters and include an uppercase letter, a number, and a special character.')" oninput="this.setCustomValidity(''); checkPasswords()" placeholder="Enter password here">        
            <label>Confirm Password</label>
            <input name="confpw" class="confpw" type="password" pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$" required  oninvalid="this.setCustomValidity('Password must be at least 8 characters and include an uppercase letter, a number, and a special character.')" oninput="this.setCustomValidity(''); checkPasswords()" placeholder="Confirm password here">        
            <p id="pwError" style="color:red; display:none;">Passwords must match</p>

                            
 
            <input name="adduser" class="submitbtn" type="submit" value="Submit">
 

        </form>
                                <script>

                function checkPasswords(){
                const pw=document.querySelector(".pw");
                const confpw=document.querySelector(".confpw");
                const error = document.getElementById("pwError");

                if(pw.value!=confpw.value){
                     
                    document.querySelector('.submitbtn').disabled=true;
                    error.style.display="block";
                }
                else{
                    document.querySelector('.submitbtn').disabled=false;
                    error.style.display="none";

                }
            }
            </script>
                                 <?php 
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitbtn'])) {
            $pw=$_POST["pw"];
            $confpw=$_POST["confpw"];
            $confirm=$pw==$confpw ? " ":"Passwords must match";
            }


        
?>

 
        </div>
        <p class="paragraph">Already got an account? <a style="color:maroon" href="index.php">Log in</a></p>
        <p class="paragraph"><a style="color:maroon;" href="forgotpassword.php">Forgot Passsword?</a></p>

        </div>
        </div>
<div class="footer">By Kallam Samad 2026 </div>
</body>
</html>
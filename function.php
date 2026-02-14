<?php
function addUser($db,$role_ID,$display,$fname,$mname,$lname,$email,$phone,$active=1){

    $stmt = $db->prepare("
        INSERT INTO Users
        (RoleID, DisplayName, FirstName, MiddleName, LastName, Email, Phone, IsActive)
        VALUES
        (:role_ID, :display, :fname, :mname, :lname, :email, :phone, :active)
    ");

    $stmt->bindValue(":role_ID", $role_ID, SQLITE3_INTEGER);
    $stmt->bindValue(":display", $display, SQLITE3_TEXT);
    $stmt->bindValue(":fname", $fname, SQLITE3_TEXT);
    $stmt->bindValue(":mname", $mname, SQLITE3_TEXT);
    $stmt->bindValue(":lname", $lname, SQLITE3_TEXT);
    $stmt->bindValue(":email", $email, SQLITE3_TEXT);
    $stmt->bindValue(":phone", $phone, SQLITE3_TEXT);
    $stmt->bindValue(":active", $active, SQLITE3_INTEGER);

    $stmt->execute();
    return $db->lastInsertRowID();
}

function addCredentials($db, $userID, $username, $plainPassword) {

    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO Credentials
        (UserID, Username, HashedPassword, PasswordChangedAt, FailedLoginAttempts)
        VALUES
        (:userID, :username, :hash, CURRENT_TIMESTAMP, 0)
    ");

    $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
    $stmt->bindValue(":username", $username, SQLITE3_TEXT);
    $stmt->bindValue(":hash", $hash, SQLITE3_TEXT);

    $stmt->execute();
}
?>
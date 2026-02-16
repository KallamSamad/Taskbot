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
        (UserID, Username, HashedPassword, PasswordChangedAt)
        VALUES
        (:userID, :username, :hash, CURRENT_TIMESTAMP)
    ");

    $stmt->bindValue(":userID", $userID, SQLITE3_INTEGER);
    $stmt->bindValue(":username", $username, SQLITE3_TEXT);
    $stmt->bindValue(":hash", $hash, SQLITE3_TEXT);

    $stmt->execute();
}

function code($db,$code,$id){
    $code = rand(10000000,99999999);
    $stmt = $db->prepare("UPDATE Credentials SET ResetToken = :code WHERE UserID = :id");
    $stmt->bindValue(":code",$code, SQLITE3_TEXT);
    $stmt->bindValue(":id",$id, SQLITE3_INTEGER);
    $stmt->execute();
    return $code;
}

function updatePW($db,$id,$plainPassword){
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        UPDATE Credentials
        SET HashedPassword = :pw,
            PasswordChangedAt = CURRENT_TIMESTAMP,
            ResetToken = NULL
        WHERE UserID = :id
    ");

    $stmt->bindValue(":pw",$hash, SQLITE3_TEXT);
    $stmt->bindValue(":id",$id, SQLITE3_INTEGER);
    $stmt->execute();
}
?>
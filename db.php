<?php
$db = new SQLite3("C:/xampp/htdocs/TaskBot/database.db");
$db->busyTimeout(10000);
$db->exec("PRAGMA foreign_keys = ON;");
?>
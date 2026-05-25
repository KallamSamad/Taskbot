<?php
$db = new SQLite3(__DIR__ . "/data/database.db");
$db->busyTimeout(10000);
$db->exec("PRAGMA foreign_keys = ON;");
?>
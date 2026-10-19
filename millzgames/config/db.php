<?php
$host = 'sql213.infinityfree.com';
$db   = 'if0_43197002_millzgames';
$user = 'if0_43197002';
$pass = 'WEKA_PASSWORD_YAKO_HAPA'; // ← Password yako halisi

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
<?php
// MySQL connection
$servername = "sql103.infinityfree.com";
$username   = "if0_40362458";
$password   = "zVArcPZo8nw";
$dbname     = "if0_40362458_db_tech1";
$port       = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Thiết lập charset UTF-8
$conn->set_charset('utf8mb4');
?>

<?php
// MySQL connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techshop_ai";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}
// Thiết lập charset UTF-8
$conn->set_charset('utf8mb4');
?>
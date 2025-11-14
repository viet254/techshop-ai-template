<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT id, name, price, image, description, specs, category, stock FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
echo json_encode($product ?: new stdClass());
?>
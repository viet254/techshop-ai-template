<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../includes/cart_service.php';
$items = cart_fetch_items($conn);
$summary = cart_calculate_summary($items);
$saved = cart_fetch_saved_items($conn);
echo json_encode([
    'cart' => cart_items_to_map($items),
    'items' => $items,
    'summary' => $summary,
    'saved' => $saved
]);
?>
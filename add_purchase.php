<?php
session_start();
require '../database/db.php';

$supplier_id = $_POST['supplier_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$cost_price = $_POST['cost_price'];
$added_by = $_SESSION['user_id'];

// 1. Record the purchase
$stmt = $pdo->prepare("
    INSERT INTO purchases (supplier_id, product_id, quantity, cost_price, added_by)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$supplier_id, $product_id, $quantity, $cost_price, $added_by]);

// 2. Update or insert into inventory
$check = $pdo->prepare("SELECT id, total_quantity FROM inventory WHERE product_id = ?");
$check->execute([$product_id]);
$inventory = $check->fetch();

if ($inventory) {
    // Update quantity
    $newQty = $inventory['total_quantity'] + $quantity;
    $pdo->prepare("UPDATE inventory SET total_quantity = ?, last_updated = NOW() WHERE product_id = ?")
        ->execute([$newQty, $product_id]);
} else {
    // Insert new
    $pdo->prepare("INSERT INTO inventory (product_id, total_quantity) VALUES (?, ?)")
        ->execute([$product_id, $quantity]);
}

header("Location: ../public/dashboard.php");
exit();

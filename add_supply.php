<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/index.php');
    exit();
}

$product_id = $_POST['product_id'];
$supplier_id = $_POST['supplier_id'];
$quantity = $_POST['quantity'];
$cost_price = $_POST['cost_price'];
$supply_date = $_POST['supply_date'] ?: date('Y-m-d H:i:s');
$threshold = $_POST['low_stock_threshold'];

// 1. Insert into product_supply
$insertSupply = $pdo->prepare("
    INSERT INTO product_supply (product_id, supplier_id, quantity, cost_price, supply_date, low_stock_threshold)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insertSupply->execute([$product_id, $supplier_id, $quantity, $cost_price, $supply_date, $threshold]);

// 2. Get the ID of the new product_supply row
$product_supply_id = $pdo->lastInsertId();

// 3. Insert into inventory (actual stock)
$insertInventory = $pdo->prepare("
    INSERT INTO inventory (product_supply_id, product_id, supplier_id, quantity, cost_price, supply_date)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insertInventory->execute([$product_supply_id, $product_id, $supplier_id, $quantity, $cost_price, $supply_date]);

// 4. Redirect or notify
header("Location: ../public/view_supplies.php?success=1");
exit();

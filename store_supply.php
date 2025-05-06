<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = $_POST['product_id'] ?? null;
    $supplier_id = $_POST['supplier_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 0;
    $cost_price = $_POST['cost_price'] ?? 0.00;
    $supply_date = $_POST['supply_date'] ?? date('Y-m-d');

    if ($product_id && $supplier_id && $quantity > 0 && $cost_price > 0) {
        $stmt = $pdo->prepare("INSERT INTO product_supply (product_id, supplier_id, quantity, cost_price, supply_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $supplier_id, $quantity, $cost_price, $supply_date]);

        header("Location: view_supplies.php");
        exit();
    } else {
        echo "Invalid input. Please check your entries.";
    }
} else {
    header("Location: add_supply.php");
    exit();
}

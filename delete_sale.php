<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saleId = $_POST['id'] ?? null;

    if (!$saleId) {
        die("Missing sale ID.");
    }

    // Fetch the sale to restore its quantity
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
    $stmt->execute([$saleId]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        die("Sale not found.");
    }

    $productId = $sale['product_id'];
    $quantity = $sale['quantity'];

    // Restore the quantity back to product_supply (reverse FIFO)
    $supplies = $pdo->prepare("SELECT id FROM product_supply WHERE product_id = ? ORDER BY supply_date DESC");
    $supplies->execute([$productId]);
    $supplyRows = $supplies->fetchAll(PDO::FETCH_ASSOC);

    foreach ($supplyRows as $supply) {
        $pdo->prepare("UPDATE product_supply SET quantity = quantity + ? WHERE id = ?")
            ->execute([$quantity, $supply['id']]);
        break; // Assume we put the full quantity back into one supply entry
    }

    // Delete the sale
    $deleteStmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
    $deleteStmt->execute([$saleId]);

    header("Location: view_sales.php");
    exit();
}
?>

<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $product_id = $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    $sale_price = (float) $_POST['sale_price'];
    $sale_date = $_POST['sale_date'];
    $sold_by = $_SESSION['user_id'];

    // Begin transaction to ensure atomic operation
    $pdo->beginTransaction();

    try {
        // Insert sale record
        $stmt = $pdo->prepare("INSERT INTO sales (customer_id, product_id, quantity, sale_price, sale_date, sold_by) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_id, $product_id, $quantity, $sale_price, $sale_date, $sold_by]);

        // Deduct from product_supply (FIFO logic)
        $remaining = $quantity;
        $supplyStmt = $pdo->prepare("SELECT id, quantity FROM product_supply 
                                     WHERE product_id = ? AND quantity > 0 
                                     ORDER BY supply_date ASC");
        $supplyStmt->execute([$product_id]);
        $supplies = $supplyStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($supplies as $supply) {
            if ($remaining <= 0) break;

            $deduct = min($remaining, $supply['quantity']);
            $updateStmt = $pdo->prepare("UPDATE product_supply SET quantity = quantity - ? WHERE id = ?");
            $updateStmt->execute([$deduct, $supply['id']]);

            $remaining -= $deduct;
        }

        if ($remaining > 0) {
            // Not enough stock
            $pdo->rollBack();
            die("❌ Not enough stock available to complete the sale.");
        }

        $pdo->commit();
        header('Location: ../public/view_sales.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("❌ Error recording sale: " . $e->getMessage());
    }
} else {
    header('Location: ../public/sales.php');
    exit();
}
?>

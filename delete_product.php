<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $productId = $_POST['id'];

    // First, check if product is referenced in sales
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE product_id = ?");
    $checkStmt->execute([$productId]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        echo "<script>alert('❌ Cannot delete product — it is referenced in sales records.'); window.location.href='stocks.php';</script>";
        exit();
    }

    // If not referenced, allow deletion
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$productId]);

    echo "<script>alert('✅ Product deleted successfully.'); window.location.href='stocks.php';</script>";
} else {
    header('Location: stocks.php');
    exit();
}
?>

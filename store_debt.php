<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerId = $_POST['customer_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $description = $_POST['description'] ?? '';
    $addedBy = $_SESSION['user_id'];

    if ($customerId && is_numeric($amount) && $amount > 0) {
        // Insert debt into the database
        $stmt = $pdo->prepare("
            INSERT INTO debts (customer_id, amount, amount_owed, amount_paid, description, added_by, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $customerId,
            $amount,
            $amount,       // amount_owed = amount
            0.00,          // amount_paid initially 0
            $description,
            $addedBy,
            'unpaid'
        ]);

        header("Location: view_debts.php");
        exit();
    } else {
        echo "Invalid data provided.";
    }
} else {
    header("Location: add_debt.php");
    exit();
}

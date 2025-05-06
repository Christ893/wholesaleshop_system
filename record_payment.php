<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debtId = $_POST['debt_id'];
    $amountPaid = $_POST['amount_paid'];
    $userId = $_SESSION['user_id'];

    // Add payment
    $stmt = $pdo->prepare("INSERT INTO debt_payments (debt_id, amount_paid, received_by) VALUES (?, ?, ?)");
    $stmt->execute([$debtId, $amountPaid, $userId]);

    // Recalculate total paid
    $stmt = $pdo->prepare("SELECT amount FROM debts WHERE id = ?");
    $stmt->execute([$debtId]);
    $originalAmount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM debt_payments WHERE debt_id = ?");
    $stmt->execute([$debtId]);
    $totalPaid = $stmt->fetchColumn();

    if ($totalPaid >= $originalAmount) {
        $stmt = $pdo->prepare("UPDATE debts SET status = 'paid', paid_at = NOW() WHERE id = ?");
        $stmt->execute([$debtId]);
    }

    header("Location: view_debts.php");
    exit();
}
?>

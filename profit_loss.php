<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get filter inputs
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$whereClause = '';
$params = [];

// Build WHERE clause if dates are selected
if ($startDate && $endDate) {
    $whereClause = "WHERE created_at BETWEEN ? AND ?";
    $params = [$startDate . " 00:00:00", $endDate . " 23:59:59"];
}

// Total sales
$salesStmt = $pdo->prepare("SELECT SUM(quantity * sale_price) AS total_sales FROM sales $whereClause");
$salesStmt->execute($params);
$totalSales = $salesStmt->fetch()['total_sales'] ?? 0;

// Total purchases - FIX: changed 'price' to 'cost_price'
$purchaseStmt = $pdo->prepare("SELECT SUM(quantity * cost_price) AS total_purchase FROM product_supply $whereClause");
$purchaseStmt->execute($params);
$totalPurchase = $purchaseStmt->fetch()['total_purchase'] ?? 0;

// Vehicle expenses
$expenseStmt = $pdo->prepare("SELECT SUM(amount) AS total_expense FROM vehicle_expenses $whereClause");
$expenseStmt->execute($params);
$totalExpenses = $expenseStmt->fetch()['total_expense'] ?? 0;

// Net profit/loss
$netProfit = $totalSales - ($totalPurchase + $totalExpenses);
?>
<a href="dashboard.php">Back to dashboard</a>

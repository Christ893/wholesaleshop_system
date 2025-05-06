<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Filters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$filterClause = '';
$params = [];

if ($startDate && $endDate) {
    $filterClause = "WHERE expense_date BETWEEN ? AND ?";
    $params = [$startDate, $endDate];
}

$query = "SELECT description, amount, expense_date FROM vehicle_expenses $filterClause ORDER BY expense_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total
$totalQuery = "SELECT SUM(amount) AS total FROM vehicle_expenses $filterClause";
$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute($params);
$totalSpent = $totalStmt->fetch()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Expense Report</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 8px;
            border: 1px solid #ccc;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .summary {
            margin-top: 20px;
            background: #f0f0f0;
            padding: 12px;
            font-weight: bold;
        }
        a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🚛 Vehicle Expense Report</h2>

    <form method="GET">
        <div class="form-group">
            <label>From:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
            <label>To:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
            <button type="submit">Filter</button>
        </div>
        <a href="dashboard.php">← Back to Dashboard</a> </br>
        <a href="reports.php">← Back to Report</a>
    </form>

    <?php if ($expenses): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Amount (CFA)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $exp): ?>
                    <tr>
                        <td><?= htmlspecialchars($exp['expense_date']) ?></td>
                        <td><?= htmlspecialchars($exp['description']) ?></td>
                        <td><?= number_format($exp['amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary">
            Total Spent: CFA<?= number_format($totalSpent, 2) ?>
        </div>
    <?php else: ?>
        <p>No expenses found for this period.</p>
    <?php endif; ?>
</div>
</body>
</html>

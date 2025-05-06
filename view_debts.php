<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize debts array to avoid undefined errors
$debts = [];

$stmt = $pdo->query("
    SELECT d.id, c.name AS customer_name, d.amount AS amount, d.description,
           d.created_at, d.status
    FROM debts d
    JOIN customers c ON d.customer_id = c.id
    ORDER BY d.created_at DESC
");

if ($stmt) {
    $debts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Debts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: #fff;
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        input[type="number"] {
            width: 80px;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #27ae60;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #219150;
        }

        em {
            color: green;
            font-weight: bold;
        }

        a {
            display: inline-block;
            margin-bottom: 15px;
            color: #2980b9;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>💰 Customer Debts</h2>
    <a href="dashboard.php">← Back to Dashboard</a>

    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Amount Owed</th>
                <th>Amount Paid</th>
                <th>Balance</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($debts as $debt): 
                // Get total amount paid from debt_payments
                $stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM debt_payments WHERE debt_id = ?");
                $stmt->execute([$debt['id']]);
                $totalPaid = $stmt->fetchColumn() ?: 0;
                $balance = $debt['amount'] - $totalPaid;
                $isPaid = $debt['status'] === 'paid';
            ?>
                <tr>
                    <td><?= htmlspecialchars($debt['customer_name']) ?></td>
                    <td><?= number_format($debt['amount'], 2) ?></td>
                    <td><?= number_format($totalPaid, 2) ?> paid</td>
                    <td><?= number_format($balance, 2) ?> due</td>
                    <td><?= htmlspecialchars($debt['description']) ?></td>
                    <td><?= $debt['created_at'] ?></td>
                    <td><?= $debt['status'] ?></td>
                    <td>
                        <?php if (!$isPaid): ?>
                            <form method="POST" action="record_payment.php" style="display:inline;">
                                <input type="hidden" name="debt_id" value="<?= $debt['id'] ?>">
                                <input type="number" name="amount_paid" step="0.01" placeholder="Amount" required>
                                <button type="submit">💵 Record Payment</button>
                            </form>
                        <?php else: ?>
                            <em>Fully Paid</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

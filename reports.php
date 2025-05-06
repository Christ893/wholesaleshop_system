<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f5f9;
            margin: 0;
            padding: 30px;
            display: flex;
            justify-content: center;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            color: #003344;
            margin-bottom: 30px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .report-card {
            background-color: #e6f3f8;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: #003344;
        }

        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #d8edf6;
        }

        .report-card h3 {
            margin: 0 0 10px;
        }

        .report-card p {
            margin: 0;
            color: #555;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Reports Dashboard</h2>

    <div class="card-grid">
        <a href="product_profit_report.php" class="report-card">
            <h3>📦 Product Profit Report</h3>
            <p>View profits/losses by product over a selected period.</p>
        </a>

        <a href="supplier_report.php" class="report-card">
            <h3>🚚 Supplier Report</h3>
            <p>See supply costs and quantities per supplier.</p>
        </a>

        <a href="customer_report.php" class="report-card">
            <h3>👤 Customer Report</h3>
            <p>Track purchases and outstanding balances by customer.</p>
        </a>

        <a href="vehicle_expense_report.php" class="report-card">
            <h3>🚗 Vehicle Expense Report</h3>
            <p>Track delivery-related fuel and maintenance expenses.</p>
        </a>

        <a href="sales_summary.php" class="report-card">
            <h3>💰 Sales Summary</h3>
            <p>View overall sales revenue, cost, and profit.</p>
        </a>
    </div>
</div>
</body>
</html>

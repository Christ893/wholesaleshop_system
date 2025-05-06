<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../database/db.php';

$username = $_SESSION['username'] ?? 'User';

// Fetch low stock products
 // Fetch low stock products using inventory and product_supply
 $lowStockStmt = $pdo->query("
 SELECT 
     p.name,
     ps_total.total_quantity,
     ps_threshold.low_stock_threshold,
     ps_threshold.supply_date
 FROM (
     SELECT product_id, SUM(quantity) AS total_quantity
     FROM product_supply
     GROUP BY product_id
 ) AS ps_total
 JOIN products p ON p.id = ps_total.product_id
 JOIN (
     SELECT ps1.*
     FROM product_supply ps1
     JOIN (
         SELECT product_id, MAX(supply_date) AS latest_date
         FROM product_supply
         GROUP BY product_id
     ) ps2 ON ps1.product_id = ps2.product_id AND ps1.supply_date = ps2.latest_date
 ) AS ps_threshold ON ps_total.product_id = ps_threshold.product_id
 WHERE ps_total.total_quantity <= ps_threshold.low_stock_threshold
");
$lowStockProducts = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);


// Sales chart data: total sales per day
$salesChartData = $pdo->query("SELECT DATE(sale_date) as date, SUM(quantity * sale_price) as total_sales FROM sales GROUP BY DATE(sale_date) ORDER BY DATE(sale_date) ASC")->fetchAll(PDO::FETCH_ASSOC);

$salesLabels = json_encode(array_column($salesChartData, 'date'));
$salesValues = json_encode(array_map('floatval', array_column($salesChartData, 'total_sales')));

// Stock chart data
$stockData = $pdo->query("
    SELECT p.name, SUM(ps.quantity) AS total_quantity
    FROM product_supply ps
    JOIN products p ON ps.product_id = p.id
    GROUP BY ps.product_id
")->fetchAll(PDO::FETCH_ASSOC);

$stockLabels = json_encode(array_column($stockData, 'name'));
$stockValues = json_encode(array_map('intval', array_column($stockData, 'total_quantity')));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Wholesale System</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        .top-bar { background-color: #004466; color: white; padding: 15px 20px; text-align: right; font-size: 18px; }
        .container { display: flex; height: calc(100vh - 60px); }
        .sidebar {
            width: 220px;
            background-color: #f4f4f4;
            padding: 20px;
            border-right: 1px solid #ccc;
        }
        .sidebar a {
            display: block;
            margin: 10px 0;
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        .sidebar a:hover { color: #004466; }
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #fafafa;
            overflow-y: auto;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        canvas { max-width: 100%; height: 300px; }
    </style>
</head>
<body>

    <div class="top-bar">
        Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>
    </div>


    <div class="container">
        <div class="sidebar">
            <a href="dashboard.php">Dashboard</a>
            <a href="add_user.php">➕ Add New User</a>
            <a href="register_customer.php">➕ Add New Customer</a>
            <a href="customers.php">👥 Customers</a>
            <a href="customer_sales_report.php">👥 Customer Sales Report</a>
            <a href="suppliers.php">➕ Add Suppliers</a>
            <a href="add_supply.php">➕ Add Product Supply</a>
            <a href="view_supplies.php">📦 View Product Supply</a>
            <a href="products.php">📦 Add Products</a>
            <a href="sales.php">💰 Sales</a>
            <a href="view_sales.php">💰 View Sales</a>
            <a href="vehicle_expenses.php">🚛 Vehicle Expenses</a>
            <a href="reports.php">📊 Reports</a>
            <a href="deliveries.php">🚛 Delivery</a>
            <!-- <a href="profit_loss.php">💰 Profit / Loss</a> -->
            <a href="add_debt.php">➕ Add Debts</a>
            <a href="view_debts.php">💰 View Debts</a>
            <a href="stocks.php">📦 Stocks</a>
            <a href="logout.php">Logout</a>
        </div>

        <div class="main-content">
            <div class="card">
                <h2>📉 Sales Overview</h2>
                <canvas id="salesChart"></canvas>
            </div>

            <div class="card">
                <h2>📦 Stock Overview</h2>
                <canvas id="stockChart"></canvas>
            </div>

            <div class="card">
                <h2>⚠️ Low Stock Alerts</h2>
                <?php if (count($lowStockProducts) > 0): ?>
                    <ul>
                        <?php foreach ($lowStockProducts as $product): ?>
                            <li>
                                <strong><?= htmlspecialchars($product['name']) ?></strong> — 
                                only <span style="color: red;"><?= $product['total_quantity'] ?></span> left 
                                (threshold: <?= $product['low_stock_threshold'] ?>)
                                <?php if (!empty($product['supply_date'])): ?>
                                    — last supplied on <?= date("d-M-Y", strtotime($product['supply_date'])) ?>
                                <?php endif; ?>
                            </li>

                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: green;">All stocks are above threshold. 👍</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo $salesLabels; ?>,
                datasets: [{
                    label: 'Total Sales',
                    data: <?php echo $salesValues; ?>,
                    borderColor: 'rgba(0, 102, 204, 1)',
                    backgroundColor: 'rgba(0, 102, 204, 0.2)',
                    fill: true
                }]
            },
            options: { responsive: true, plugins: { legend: { display: true } } }
        });

        const stockCtx = document.getElementById('stockChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $stockLabels; ?>,
                datasets: [{
                    label: 'Stock Quantity',
                    data: <?php echo $stockValues; ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.7)'
                }]
            },
            options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>

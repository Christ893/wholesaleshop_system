<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$filterClause = '';
$params = [];

if ($startDate && $endDate) {
    $filterClause = "AND s.sale_date BETWEEN ? AND ?";
    $params = [$startDate . " 00:00:00", $endDate . " 23:59:59"];
}

// Product-level summary
$query = "
    SELECT 
        p.name AS product_name,
        SUM(s.quantity) AS total_quantity_sold,
        SUM(s.quantity * s.sale_price) AS total_revenue
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE 1=1 $filterClause
    GROUP BY p.id, p.name
    ORDER BY total_revenue DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$productSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Customer-level breakdown
$queryCustomer = "
    SELECT 
        c.name AS customer_name,
        SUM(s.quantity) AS total_quantity,
        SUM(s.quantity * s.sale_price) AS total_sales
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    WHERE 1=1 $filterClause
    GROUP BY c.id, c.name
    ORDER BY total_sales DESC
";
$stmtCustomer = $pdo->prepare($queryCustomer);
$stmtCustomer->execute($params);
$customerSummary = $stmtCustomer->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Summary</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background-color: #f4f6f8; }
        .container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
        h2 { text-align: center; }
        .flex-row { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
        canvas { margin-top: 30px; }
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
    <h2>📊 Sales Summary</h2>
    <a href="dashboard.php">← Back to Dashboard</a> </br>
    <a href="reports.php">← Back to Report</a>

    <form method="GET" class="flex-row">
        <label>From: <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required></label>
        <label>To: <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required></label>
        <button type="submit">Filter</button>
        <button type="button" onclick="print()">🖨️ Print</button>
        <button type="button" onclick="exportToExcel()">📁 Export to Excel</button>
    </form>

    <h3>Top-Selling Products</h3>
    <table id="summaryTable">
        <thead><tr><th>Product</th><th>Quantity Sold</th><th>Total Revenue (CFA)</th></tr></thead>
        <tbody>
        <?php foreach ($productSummary as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['total_quantity_sold'] ?></td>
                <td><?= number_format($row['total_revenue'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <canvas id="productChart" height="120"></canvas>

    <h3>Customer Breakdown</h3>
    <table>
        <thead><tr><th>Customer</th><th>Quantity Bought</th><th>Total Sales (CFA)</th></tr></thead>
        <tbody>
        <?php foreach ($customerSummary as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['customer_name']) ?></td>
                <td><?= $c['total_quantity'] ?></td>
                <td><?= number_format($c['total_sales'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
const productChartCtx = document.getElementById('productChart').getContext('2d');
const productChart = new Chart(productChartCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($productSummary, 'product_name')) ?>,
        datasets: [{
            label: 'Quantity Sold',
            data: <?= json_encode(array_map('intval', array_column($productSummary, 'total_quantity_sold'))) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: { y: { beginAtZero: true } },
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

function exportToExcel() {
    const table = document.getElementById("summaryTable");
    const wb = XLSX.utils.table_to_book(table, { sheet: "Summary" });
    XLSX.writeFile(wb, "sales_summary.xlsx");
}
</script>
</body>
</html>

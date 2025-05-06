<?php
require '../database/db.php';

// Filters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$customerId = $_GET['customer_id'] ?? '';

$whereClauses = [];
$params = [];

if (!empty($startDate)) {
    $whereClauses[] = "s.sale_date >= ?";
    $params[] = $startDate . " 00:00:00";
}
if (!empty($endDate)) {
    $whereClauses[] = "s.sale_date <= ?";
    $params[] = $endDate . " 23:59:59";
}
if (!empty($customerId)) {
    $whereClauses[] = "s.customer_id = ?";
    $params[] = $customerId;
}

$whereSQL = $whereClauses ? "WHERE " . implode(" AND ", $whereClauses) : "";

$query = "
    SELECT c.name AS customer_name, p.name AS product_name, s.quantity, s.sale_date,
           d.delivered, d.delivery_date
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    JOIN products p ON s.product_id = p.id
    LEFT JOIN deliveries d ON s.id = d.sale_id
    $whereSQL
    ORDER BY s.sale_date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Excel headers
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=deliveries_" . date("Y-m-d") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr><th>Customer</th><th>Product</th><th>Quantity</th><th>Sale Date</th><th>Delivered</th><th>Delivery Date</th></tr>";

foreach ($sales as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
    echo "<td>" . $row['quantity'] . "</td>";
    echo "<td>" . $row['sale_date'] . "</td>";
    echo "<td>" . ($row['delivered'] ? 'Yes' : 'No') . "</td>";
    echo "<td>" . ($row['delivery_date'] ?? '-') . "</td>";
    echo "</tr>";
}
echo "</table>";
exit;

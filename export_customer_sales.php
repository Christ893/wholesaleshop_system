<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$customerId = $_POST['customer_id'] ?? '';
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$exportType = $_POST['export_type'] ?? 'csv';

if (!$customerId || !$startDate || !$endDate) {
    die("Missing parameters.");
}

// Fetch customer name
$stmt = $pdo->prepare("SELECT name FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();
$customerName = $customer ? $customer['name'] : 'Unknown Customer';

// Fetch sales
$stmt = $pdo->prepare("
    SELECT s.sale_date, p.name AS product_name, s.quantity, s.sale_price
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.customer_id = ? AND s.sale_date BETWEEN ? AND ?
    ORDER BY s.sale_date DESC
");
$stmt->execute([$customerId, $startDate, $endDate]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export logic
if ($exportType === 'csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment;filename=sales_report.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ['Date', 'Product', 'Qty', 'Unit Price', 'Total']);

    foreach ($sales as $sale) {
        $total = $sale['quantity'] * $sale['sale_price'];
        fputcsv($output, [
            $sale['sale_date'],
            $sale['product_name'],
            $sale['quantity'],
            number_format($sale['sale_price'], 2),
            number_format($total, 2)
        ]);
    }

    fclose($output);
    exit;
}

// Export as PDF
require '../vendor/dompdf-3.1.0/dompdf/autoload.php'; // TCPDF or Dompdf installed via Composer

use Dompdf\Dompdf;

$html = "<h3>Customer Sales Report: {$customerName}</h3>";
$html .= "<p>From: {$startDate} To: {$endDate}</p>";
$html .= "<table border='1' cellspacing='0' cellpadding='5'>";
$html .= "<thead><tr><th>Date</th><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead><tbody>";

$total = 0;
foreach ($sales as $sale) {
    $lineTotal = $sale['quantity'] * $sale['sale_price'];
    $total += $lineTotal;
    $html .= "<tr>
                <td>{$sale['sale_date']}</td>
                <td>{$sale['product_name']}</td>
                <td>{$sale['quantity']}</td>
                <td>" . number_format($sale['sale_price'], 2) . "</td>
                <td>" . number_format($lineTotal, 2) . "</td>
             </tr>";
}

$html .= "</tbody></table>";
$html .= "<p><strong>Total: ₦" . number_format($total, 2) . "</strong></p>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("sales_report_{$customerName}.pdf", ["Attachment" => 1]);
exit;

<?php
session_start();
require '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';

    // Simple validation
    if (empty($name)) {
        die("Supplier name is required.");
    }

    $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact, address) VALUES (?, ?, ?)");
    $stmt->execute([$name, $contact, $address]);

    header("Location: ../public/suppliers.php");
    exit();
}
?>

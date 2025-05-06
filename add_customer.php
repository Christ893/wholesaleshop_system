<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$name = $_POST['name'];
$contact = $_POST['contact'] ?? null;
$address = $_POST['address'] ?? null;

$stmt = $pdo->prepare("INSERT INTO customers (name, contact, address) VALUES (?, ?, ?)");
$stmt->execute([$name, $contact, $address]);

header("Location: ../public/customers.php");
exit();

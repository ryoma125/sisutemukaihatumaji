<?php
require "../require/db-connect.php";

$pdo = new PDO($dsn, $user, $pass);

$keyword = $_GET['q'] ?? '';

if ($keyword === '') {
    echo json_encode([]);
    exit;
}

$sql = "SELECT product_name FROM products WHERE product_name LIKE :kw LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":kw", "%$keyword%", PDO::PARAM_STR);
$stmt->execute();

$names = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($names);

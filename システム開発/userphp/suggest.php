<?php
header('Content-Type: application/json');
require_once '../require/dbconnect.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";

if ($keyword === "") {
    echo json_encode([]);
    exit;
}

// 複数キーワード対応（最初の1語だけ候補に使う）
$word = explode(" ", $keyword)[0];

$sql = "SELECT product_name FROM products
        WHERE product_name LIKE :word
        ORDER BY product_name ASC
        LIMIT 5";

$stmt = $dbh->prepare($sql);
$stmt->execute([":word" => "%{$word}%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
exit;
?>

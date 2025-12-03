<?php
session_start();
require "../require/db-connect.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    die("ログインしていません");
}

$user_id = $_SESSION['user_id'];

$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 商品IDがPOSTで届いているか？
if (!isset($_POST['product_id'])) {
    die("商品が指定されていません。");
}

$product_id = (int)$_POST['product_id'];

// ========================
// カート内に同じ商品があるか確認
// ========================
$stmt = $pdo->prepare("SELECT * FROM Cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // 存在 → 数量＋1
    $update = $pdo->prepare("UPDATE Cart SET quantity = quantity + 1 WHERE cart_id = ?");
    $update->execute([$existing['cart_id']]);
} else {
    // 新規追加
    $insert = $pdo->prepare("INSERT INTO Cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $insert->execute([$user_id, $product_id]);
}

// カートページへ移動
header("Location: cart.php");
exit;

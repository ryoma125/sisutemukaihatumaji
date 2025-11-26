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

// 商品IDを受け取る
if (!isset($_POST['product_id'])) {
  die("商品が指定されていません。");
}

$product_id = (int)$_POST['product_id'];

// ========================
// 同じ商品がすでにカートにあるか確認
// ========================
$stmt = $pdo->prepare("SELECT * FROM Cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
  // 既に存在 → 数量を+1する
  $update = $pdo->prepare("UPDATE Cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
  $update->execute([$user_id, $product_id]);
} else {
  // 新規追加
  $insert = $pdo->prepare("INSERT INTO Cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
  $insert->execute([$user_id, $product_id]);
}

// カート画面へリダイレクト
header("Location: cart.php");
exit;

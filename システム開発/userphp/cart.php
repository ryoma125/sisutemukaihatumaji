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

// ========================
// 削除処理
// ========================
if (isset($_POST['remove'])) {
    $cart_id = (int)$_POST['remove'];
    $stmt = $pdo->prepare("DELETE FROM Cart WHERE cart_id = ?");
    $stmt->execute([$cart_id]);
    header("Location: cart.php");
    exit;
}
require '../require/navigation.php';

// ========================
// カート取得（JOIN Product）
// ========================
$sql = "
  SELECT 
    Cart.cart_id,
    Cart.quantity,
    Product.product_id,
    Product.product_name,
    Product.price,
    Product.shipping_fee,
    Product.image_url
  FROM Cart
  JOIN Product ON Cart.product_id = Product.product_id
  WHERE Cart.user_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================
// 合計
// ========================
$total = 0;
foreach ($cart_items as $item) {
    $total += ($item['price'] + $item['shipping_fee']) * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>カート画面 | Calçar</title>
  <link rel="stylesheet" href="../usercss/cart.css">
</head>
<body>

<div class="cart-container">
  <div class="cart-title">カート画面</div>

  <?php if (empty($cart_items)): ?>
    <p>カートに商品がありません。</p>

  <?php else: ?>
    <?php foreach ($cart_items as $item): ?>
      <form method="POST" class="cart-item">
        <img src="<?= htmlspecialchars($item['image_url']) ?>">
        <div class="item-info">
          <h2><?= htmlspecialchars($item['product_name']) ?></h2>
          <p>価格 ¥<?= number_format($item['price']) ?></p>
          <p>送料 ¥<?= number_format($item['shipping_fee']) ?></p>
          <p>数量 <?= htmlspecialchars($item['quantity']) ?></p>
        </div>
        
        <button type="submit" name="remove" value="<?= $item['cart_id'] ?>" class="remove-btn">削除</button>
      </form>
    <?php endforeach; ?>

    <div class="total">合計金額 <?= number_format($total) ?> 円</div>

    <form action="order_confirm.php" method="post">
      <input type="hidden" name="total" value="<?= $total ?>">
      <button type="submit" class="purchase-btn">購入画面に進む</button>
    </form>

  <?php endif; ?>

</div>
<footer>  <p>&copy; 2024 Calçar. All rights reserved.</p></footer>

</body>
</html>

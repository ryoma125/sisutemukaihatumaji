<?php
session_start();
require "../require/db-connect.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id']; // 仮のユーザーID

$pdo = new PDO($connect, USER, PASS);
// ========================
// カート内商品削除
// ========================
if (isset($_POST['remove'])) {
  $remove_id = (int)$_POST['remove'];
  $stmt = $pdo->prepare("DELETE FROM Cart WHERE cart_id = ?");
  $stmt->execute([$remove_id]);
  header("Location: cart.php");
  exit;
}

// ========================
// カート内商品取得
// ========================
$stmt = $pdo->prepare("
  SELECT 
    Cart.cart_id,
    Cart.quantity,
    Product.product_name,
    Product.price,
    Product.shipping_fee,
    Product.image_url
  FROM Cart
  JOIN Product ON Cart.product_id = Product.product_id
  WHERE Cart.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================
// 合計金額計算
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
  <link rel="stylesheet" href="../require.css/navigation.css">
</head>
<body>

<header>
    <div class="logo">Calçar</div>

    <nav class="nav">
      <div class="line"></div>
      <a href="./index.php">Home/Calçar</a>
      <div class="line"></div>
      <form class="nav-search" method="get" action="/search.php">
        <label for="nav-search-input" class="sr-only">検索ワード</label>
        <input id="nav-search-input" type="text" name="q" placeholder="search" />
        <button type="submit" class="search-btn">検索</button>
      </form>
    </nav>

    <div class="icons">
      <a href="mypage.php" class="icon">👤</a>
      <a href="cart.php" class="icon">🛒</a>
    </div>
  </header>

<div class="cart-container">
  <div class="cart-title">カート画面</div>

  <?php if (empty($cart_items)): ?>
    <p>カートに商品がありません。</p>
  <?php else: ?>
    <?php foreach ($cart_items as $item): ?>
      <form method="POST" class="cart-item">
        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
        <div class="item-info">
          <h2><?= htmlspecialchars($item['product_name']) ?></h2>
          <p>価格　¥<?= number_format($item['price']) ?></p>
          <p>送料　¥<?= number_format($item['shipping_fee']) ?></p>
          <p>数量　<?= htmlspecialchars($item['quantity']) ?></p>
        </div>
        <button class="remove-btn" type="submit" name="remove" value="<?= $item['cart_id'] ?>">削除</button>
      </form>
    <?php endforeach; ?>

    <div class="total">
      合計金額　<?= number_format($total) ?>円
    </div>
      <form action="order_confirm.php" method="post">
    <input type="hidden" name="total" value="<?= $total ?>">
    <button type="submit" class="purchase-btn">購入画面に進む</button>
</form>

  <?php endif; ?>
</div>

</body>
</html>

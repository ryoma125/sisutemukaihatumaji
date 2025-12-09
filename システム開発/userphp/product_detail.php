<?php
session_start();
require_once '../require/db-connect.php';
require '../require/navigation.php';
try {
  $pdo = new PDO($connect, USER, PASS);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // 商品ID取得
  $id = isset($_GET['id']) ? intval($_GET['id']) : 1;

  $stmt = $pdo->prepare("SELECT * FROM Product WHERE product_id = :id");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $product = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$product) {
    die("商品が見つかりません。");
  }

} catch (PDOException $e) {
  echo "データベースエラー: " . htmlspecialchars($e->getMessage());
  exit;
}

/* ---------------------------------------------------
   商品コードからカラー・素材を抽出
   例: 2-BLA-22A-LEA
--------------------------------------------------- */
$codeParts = explode("-", $product["product_code"]);

// 安全対策
$colorCode = $codeParts[1] ?? "";
$materialCode = $codeParts[3] ?? "";

// カラー変換表
$colorList = [
  "BLA" => "黒",
  "WHT" => "白",
  "RED" => "赤",
  "BLU" => "青",
  "GRN" => "緑",
  "BRN" => "茶",
  "BEI" => "ベージュ",
  "GRY" => "グレー",
  "YEL" => "黄",
];

// 素材変換表
$materialList = [
  "LEA" => "レザー",
  "FAB" => "ファブリック",
  "MSH" => "メッシュ",
  "SYN" => "合成皮革",
];

$colorName = $colorList[$colorCode] ?? "ー";
$materialName = $materialList[$materialCode] ?? "ー";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['product_name']) ?> | Calçar</title>
  <link rel="stylesheet" href="../usercss/product_detail.css">
</head>
<body>

<main class="main">
  <div class="left">

    <!-- 商品画像（スライダーなし） -->
  <div class="image">
    <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-img">
  </div>


    <!-- 商品情報 -->
    <div class="product-info-under">
      <div class="info-left">
        <h2 class="product-name"><?= htmlspecialchars($product['product_name']) ?></h2>
        <p class="price">価格　￥<?= number_format($product['price']) ?></p>
        <p class="shipping">送料　￥<?= number_format($product['shipping_fee']) ?></p>
      </div>

      <div class="info-right">
        <div class="size-area">
          <label for="size">サイズ選択</label>
          <select id="size">
            <option><?= htmlspecialchars($product['size']) ?></option>
          </select>
        </div>

        <form action="cart_insert.php" method="POST">
          <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
          <button type="submit" class="cart-btn">カートに入れる</button>
        </form>
      </div>
    </div>
  </div>

  <!-- 右側：商品説明・詳細 -->
  <div class="right">

    <div class="description">
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    </div>

    <table class="detail-table">
      <tr><th>ブランド</th><td><?= htmlspecialchars($product['brand']) ?></td></tr>
      <tr><th>カテゴリ</th><td><?= htmlspecialchars($product['category']) ?></td></tr>
      <tr><th>カラー</th><td><?= htmlspecialchars($colorName) ?></td></tr>
      <tr><th>素材</th><td><?= htmlspecialchars($materialName) ?></td></tr>
      <tr><th>商品コード</th><td><?= htmlspecialchars($product['product_code']) ?></td></tr>
    </table>

  </div>
</main>

<footer></footer>
</body>
</html>

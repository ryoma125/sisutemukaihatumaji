<?php
// セッション開始（一番最初に実行）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>雨におすすめ - Calçar</title>
    <link rel="stylesheet" href="../osusumecss/osusume.css">
    <link rel="stylesheet" href="../osusumecss/footer.css">
</head>
<body>

<?php 
require '../osusumerequire/navigation.php'; 
?>

<div class="banner" style="background-image: url('img/flower.jpg');">雨におすすめ</div>

<section class="products">
  <div class="product-list">
    <?php
    // Product テーブルから雨におすすめの商品を取得する想定
    require "../require/db-connect.php";
    $pdo = new PDO($connect, USER, PASS);

    $stmt = $pdo->query("
        SELECT product_id, product_name, price, image_url
        FROM Product
        WHERE product_id IN (1,2,3,4)  /* ← 必要なら変更 */
    ");

    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
      echo '<form method="POST" action="cart_add.php" class="product">';
      
      echo '<a href="product_detail.php?id=' . $product["product_id"] . '">';
      echo '<img src="' . htmlspecialchars($product["image_url"]) . '" alt="靴">';
      echo '</a>';
      
      echo '<div class="product-name">';
      echo htmlspecialchars($product["product_name"]);
      echo '</div>';

      echo '<p>価格：¥' . number_format($product["price"]) . '</p>';

      echo '<input type="hidden" name="product_id" value="' . $product["product_id"] . '">';
      echo '<button type="submit" class="btn">カートに追加</button>';
      echo '</form>';
    }
    ?>
  </div>
</section>

<section class="recommend-section">
  <div class="recommend-title">おすすめのテーマ</div>

  <div class="recommend-slider">
    <?php
    $themes = [
      ["link" => "osusume-cafe.php", "img" => "img/cafe (2).png", "text" => "カフェにおすすめ"],
      ["link" => "osusume-natu.php", "img" => "img/R.jpg", "text" => "夏におすすめ"],
      ["link" => "osusume-fuyu.php", "img" => "img/fuyu.png", "text" => "冬におすすめ"],
      ["link" => "osusume-autdoa.php", "img" => "img/autodoa.jpg", "text" => "アウトドアにおすすめ"],
      ["link" => "osusume-supot.php", "img" => "img/supotu.png", "text" => "スポーツにおすすめ"]
    ];

    foreach ($themes as $theme) {
      echo '<a href="' . htmlspecialchars($theme["link"]) . '" class="recommend-card">';
      echo '<img src="' . htmlspecialchars($theme["img"]) . '" alt="' . htmlspecialchars($theme["text"]) . '">';
      echo '<div class="text">' . htmlspecialchars($theme["text"]) . '</div>';
      echo '</a>';
    }
    ?>
  </div>
</section>

<footer></footer>

</body>
</html>

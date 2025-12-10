<?php
// セッション開始
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
require "../require/db-connect.php";
?>

<div class="banner" style="background-image: url('img/flower.jpg');">雨におすすめ</div>

<section class="products">
  <div class="product-list">

    <?php
    // ★ 固定商品の配列（DBは使わない）
    $products = [
      ["id" => 101, "img" => "../jpg/58.png",  "name" => "kuronagakutu",         "price" => 4000],
      ["id" => 102, "img" => "../jpg/59.png", "name" => "sironagakutu",          "price" => 6200],
      ["id" => 103, "img" => "../jpg/60.png", "name" => "kuro-am",          "price" => 5000],
      ["id" => 104, "img" => "../jpg/56.png",   "name" => "bulu-kutu", "price" => 5000]
    ];

    foreach ($products as $product) {

      echo '<form method="POST" action="../userphp/cart_insert.php" class="product">';

      // 画像リンク
      echo '<a href="product_detail.php?id=' . $product["id"] . '">';
      echo '<img src="' . htmlspecialchars($product["img"]) . '" alt="靴">';
      echo '</a>';

      echo '<div class="product-name">' . htmlspecialchars($product["name"]) . '</div>';
      echo '<p>価格：¥' . number_format($product["price"]) . '</p>';

      // ★ カートに送るのは「product_id だけ」でOK（DBは触らない）
      echo '<input type="hidden" name="product_id" value="' . $product["id"] . '">';

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

<footer>
    <p>&copy; 2024 Calçar. All rights reserved.</p>
</footer>

</body>
</html>

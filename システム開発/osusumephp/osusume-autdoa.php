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
    <title>アウトドアにおすすめ - Calçar</title>
    <link rel="stylesheet" href="../osusumecss/osusume.css">
    <link rel="stylesheet" href="../osusumecss/footer.css">
</head>
<body>

<?php 
// ナビゲーションを読み込み
require '../osusumerequire/navigation.php'; 
require "../require/db-connect.php";
?>

<div class="banner" style="background-image: url('img/autodoa.jpg');">アウトドアにおすすめ</div>

<section class="products">
  <div class="product-list">
    <?php
    $products = [
      ["id" => 105, "img" => "../jpg/43.png", "name" => "", "price" => 2500],
      ["id" => 106, "img" => "../jpg/44.png", "name" => "", "price" => 3200],
      ["id" => 107, "img" => "../jpg/5.png", "name" => "", "price" => 2800],
      ["id" => 108, "img" => "../jpg/2.png", "name" => "", "price" => 3000]
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
      ["link" => "osusume-supot.php", "img" => "img/supotu.png", "text" => "スポーツにおすすめ"],
      ["link" => "osusume-ame.php", "img" => "img/flower.jpg", "text" => "雨におすすめ"]
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
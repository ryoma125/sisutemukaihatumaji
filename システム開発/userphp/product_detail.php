<?php session_start();
require_once '../require/db-connect.php';


try {
  $pdo = new PDO($connect, USER, PASS);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // URLパラメータで商品IDを取得（例: ?id=1）
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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['product_name']) ?> | Calçar</title>
  <link rel="stylesheet" href="../usercss/product_detail.css">
</head>
<body>
  <header class="header">
    <div class="logo">Calçar</div>

    <nav class="nav">
      <div class="line"></div>
      <a href="./index.php">Home/Calçar</a>
      <div class="line"></div>
      <form class="nav-search" method="get" action="../userphp/search.php">
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

  <main class="main">
  <div class="left">
    <!-- 商品画像スライダー -->
    <div class="image-slider">
      <div class="slides">
        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="商品画像1" class="product-img active">
        <img src="images/<?= htmlspecialchars($product['product_code']) ?>_2.jpg" alt="商品画像2" class="product-img">
        <img src="images/<?= htmlspecialchars($product['product_code']) ?>_3.jpg" alt="商品画像3" class="product-img">
      </div>
      <div class="dots">
        <span class="active"></span>
        <span></span>
        <span></span>
      </div>
    </div>

    <!-- 商品情報（画像の下） -->
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
            <?php
              $sizes = explode(',', $product['size']);
              foreach ($sizes as $s) {
                echo "<option>" . htmlspecialchars(trim($s)) . "</option>";
              }
            ?>
          </select>
        </div>
        <form action="cart_insert.php" method="POST">
        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
        <button type="submit" class="cart-btn">カートに入れる</button>
        </form>
      </div>
    </div>
  </div>

  <!-- 右側（説明・詳細） -->
  <div class="right">
    <div class="description">
      <p>商品の説明</p>
    </div>
    <table class="detail-table">
      <tr><th>ブランド</th><td><?= htmlspecialchars($product['brand']) ?></td></tr>
      <tr><th>カテゴリ</th><td>スニーカー</td></tr>
      <tr><th>カラー</th><td>ー</td></tr>
      <tr><th>素材</th><td>ー</td></tr>
      <tr><th>商品コード</th><td><?= htmlspecialchars($product['product_code']) ?></td></tr>
    </table>
  </div>
</main>

<script>
const images = document.querySelectorAll('.product-img');
const dots = document.querySelectorAll('.dots span');
let current = 0;
function showSlide(index) {
  images.forEach((img, i) => {
    img.classList.toggle('active', i === index);
    dots[i].classList.toggle('active', i === index);
  });
}
dots.forEach((dot, i) => {
  dot.addEventListener('click', () => {
    current = i;
    showSlide(i);
  });
});
setInterval(() => {
  current = (current + 1) % images.length;
  showSlide(current);
}, 3000);

document.querySelector('.cart-btn').addEventListener('click', () => {
  alert('商品を追加しました');
});
</script>

</body>
</html>

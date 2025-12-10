<?php 
session_start(); 
require '../require/db-connect.php';
$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calçar</title>
  <link rel="stylesheet" href="../usercss/index.style.css">
</head>
<body>

  <?php require '../require/navigation.php'; ?>
  
  <main>
    <!-- 左サイズ選択 -->
    <aside class="sidebar">
      <h2>サイズ</h2>
      <div class="size-buttons">
        <a href="../../shoze_size/22Asize.php"><button>22.5cm</button></a>
        <a href="../../shoze_size/23size.php"><button>23.0cm</button></a>
        <a href="../../shoze_size/23Asize.php"><button>23.5cm</button></a>
        <a href="../../shoze_size/24size.php"><button>24.0cm</button></a>
        <a href="../../shoze_size/24Asize.php"><button>24.5cm</button></a>
        <a href="../../shoze_size/25size.php"><button>25.0cm</button></a>
        <a href="../../shoze_size/25Asize.php"><button>25.5cm</button></a>
        <a href="../../shoze_size/26size.php"><button>26.0cm</button></a>
        <a href="../../shoze_size/26Asize.php"><button>26.5cm</button></a>
        <a href="../../shoze_size/27size.php"><button>27.0cm</button></a>
        <a href="../../shoze_size/27Asize.php"><button>27.5cm</button></a>
        <a href="../../shoze_size/28size.php"><button>28.0cm</button></a>
        <a href="../../shoze_size/28Asize.php"><button>28.5cm</button></a>
        <a href="../../shoze_size/29size.php"><button>29.0cm</button></a>
        <a href="../../shoze_size/29Asize.php"><button>29.5cm</button></a>
        <a href="../../shoze_size/30size.php"><button>30.0cm</button></a>
      </div>
    </aside>
 
    <!-- 右：おすすめ商品 -->
    <section class="products">
      <h2>オススメ</h2>
      <div class="product-grid">
        <?php
        try {

          // ============================
          // ★ 重複商品名を除外し、ランダムで6商品表示 ★
          // ============================
          $sql = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_code,
                p.brand,
                p.image_url,
                p.price,
                p.stock
            FROM Product p
            INNER JOIN (
                SELECT product_name, MIN(product_id) AS min_id
                FROM Product
                WHERE stock > 0
                GROUP BY product_name
            ) AS uniq
                ON uniq.min_id = p.product_id
            ORDER BY RAND()
            LIMIT 6
          ";

          $stmt = $pdo->query($sql);
          $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          if (count($products) > 0) {
            foreach ($products as $product) {

              $image_url = !empty($product['image_url']) 
                ? htmlspecialchars($product['image_url']) 
                : 'https://via.placeholder.com/250x200?text=NoImage';
              
              $detail_link = 'product_detail.php?id=' . $product['product_id'];

              echo '<a href="' . $detail_link . '" class="product-item">';
              echo '<img src="' . $image_url . '" alt="' . htmlspecialchars($product['product_name']) . '">';
              echo '<div class="product-info">';

              if (!empty($product['brand'])) {
                echo '<div class="product-brand">' . htmlspecialchars($product['brand']) . '</div>';
              }

              echo '<div class="product-name">' . htmlspecialchars($product['product_name']) . '</div>';
              echo '<div class="product-price">¥' . number_format($product['price']) . '</div>';

              echo '</div>';
              echo '</a>';
            }
          } else {
            echo '<p style="grid-column: 1 / -1; text-align: center; padding: 40px;">現在、表示できる商品がありません。</p>';
          }

        } catch (PDOException $e) {
          echo '<p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: red;">商品の取得に失敗しました。</p>';
        }
        ?>
      </div>
    </section>
  </main>
 
  <!-- 下部テーマ部分 -->
  <section class="recommend-section">
    <div class="recommend-title">おすすめのテーマ</div>

    <div class="recommend-slider">
      <?php
      $themes = [
        ["link" => "../osusumephp/osusume-cafe.php", "img" => "../osusumephp/img/cafe (2).png", "text" => "カフェにおすすめ"],
        ["link" => "../osusumephp/osusume-natu.php", "img" => "../osusumephp/img/R.jpg", "text" => "夏におすすめ"],
        ["link" => "../osusumephp/osusume-fuyu.php", "img" => "../osusumephp/img/fuyu.png", "text" => "冬におすすめ"],
        ["link" => "../osusumephp/osusume-autdoa.php", "img" => "../osusumephp/img/autodoa.jpg", "text" => "アウトドアにおすすめ"],
        ["link" => "../osusumephp/osusume-supot.php", "img" => "../osusumephp/img/supotu.png", "text" => "スポーツにおすすめ"],
        ["link" => "../osusumephp/osusume-ame.php", "img" => "../osusumephp/img/flower.jpg", "text" => "雨におすすめ"]
      ];

      foreach ($themes as $theme) {
        echo '<div class="recommend-card">';
        echo '<a href="' . htmlspecialchars($theme["link"]) . '">';
        echo '<img src="' . htmlspecialchars($theme["img"]) . '" alt="' . htmlspecialchars($theme["text"]) . '">';
        echo '</a>';
        echo '<div class="text">' . htmlspecialchars($theme["text"]) . '</div>';
        echo '</div>';
      }
      ?>
    </div>
  </section>

  <footer>
    <p>&copy; 2024 Calçar. All rights reserved.</p>
</footer>

</body>
</html>

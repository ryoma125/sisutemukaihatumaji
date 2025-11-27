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
        <a href="../shoze_size/shoze_22Asize.php"><button>22.5cm</button></a>
        <a href="../shoze_size/shoze_23size.php"><button>23.0cm</button></a>
        <a href="../shoze_size/shoze_23.5size.php"><button>23.5cm</button></a>
        <a href="../shoze_size/shoze_24size.php"><button>24.0cm</button></a>
        <a href="../shoze_size/shoze_24.5size.php"><button>24.5cm</button></a>
        <a href="../shoze_size/shoze_25size.php"><button>25.0cm</button></a>
        <a href="../shoze_size/shoze_25.5size.php"><button>25.5cm</button></a>
        <a href="../shoze_size/shoze_26size.php"><button>26.0cm</button></a>
        <a href="../shoze_size/shoze_26.5size.php"><button>26.5cm</button></a>
        <a href="../shoze_size/shoze_27size.php"><button>27.0cm</button></a>
        <a href="../shoze_size/shoze_27.5size.php"><button>27.5cm</button></a>
        <a href="../shoze_size/shoze_28size.php"><button>28.0cm</button></a>
        <a href="../shoze_size/shoze_28.5size.php"><button>28.5cm</button></a>
        <a href="../shoze_size/shoze_29size.php"><button>29.0cm</button></a>
        <a href="../shoze_size/shoze_29.5size.php"><button>29.5cm</button></a>
        <a href="../shoze_size/shoze_30size.php"><button>30.0cm</button></a>
      </div>
    </aside>
 
    <!-- 右：おすすめ商品 -->
    <section class="products">
      <h2>オススメ</h2>
      <div class="product-grid">
        <?php
        try {
          // Productテーブルから商品を取得（在庫があるものを6件）
          $sql = "SELECT 
                    product_id,
                    product_name,
                    product_code,
                    brand,
                    image_url,
                    price,
                    stock,
                    shipping_fee
                  FROM Product
                  WHERE stock > 0
                  ORDER BY product_id DESC
                  LIMIT 6";
          
          $stmt = $pdo->query($sql);
          $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          if (count($products) > 0) {
            foreach ($products as $product) {
              // 画像URLの処理
              $image_url = !empty($product['image_url']) 
                ? htmlspecialchars($product['image_url']) 
                : 'https://via.placeholder.com/250x200?text=NoImage';
              
              // 商品詳細ページへのリンク
              $detail_link = 'product_detail.php?id=' . $product['product_id'];
              
              echo '<a href="' . $detail_link . '" class="product-item">';
              echo '<img src="' . $image_url . '" alt="' . htmlspecialchars($product['product_name']) . '">';
              echo '<div class="product-info">';
              
              // ブランド表示
              if (!empty($product['brand'])) {
                echo '<div class="product-brand">' . htmlspecialchars($product['brand']) . '</div>';
              }
              
              // 商品名
              echo '<div class="product-name">' . htmlspecialchars($product['product_name']) . '</div>';
              
              // 価格
              echo '<div class="product-price">¥' . number_format($product['price']) . '</div>';
              
              echo '</div>';
              echo '</a>';
            }
          } else {
            // 商品がない場合
            echo '<p style="grid-column: 1 / -1; text-align: center; padding: 40px;">現在、表示できる商品がありません。</p>';
          }
        } catch (PDOException $e) {
          echo '<p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: red;">商品の取得に失敗しました。</p>';
          // デバッグ用（本番環境では削除）
          // echo '<p style="font-size: 12px;">' . $e->getMessage() . '</p>';
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

  <footer></footer>
</body>
</html>
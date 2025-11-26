<?php session_start(); 
require '../require/db-connect.php';
$pdo = new PDO($connect, USER, PASS);
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

  <?php require '../require.php/navigation.php'; ?>
  

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
        <img src="https://via.placeholder.com/250x150?text=Shoes1" alt="shoes">
        <img src="https://via.placeholder.com/250x150?text=Shoes2" alt="shoes">
        <img src="https://via.placeholder.com/250x150?text=Shoes3" alt="shoes">
        <img src="https://via.placeholder.com/250x150?text=Shoes4" alt="shoes">
        <img src="https://via.placeholder.com/250x150?text=Shoes5" alt="shoes">
        <img src="https://via.placeholder.com/250x150?text=Shoes6" alt="shoes">
      </div>
    </section>
  </main>

  <!-- 下部テーマ部分 -->
<section class="recommend-section">
  <div class="recommend-title">おすすめのテーマ</div>

  <div class="recommend-slider">
    <?php
    $themes = [
      ["link" => "osusume-cafe.php", "img" => "img/cafe (2).png", "text" => "カフェにおすすめ"],
      ["link" => "osusume-natu.php", "img" => "img/R.jpg", "text" => "夏におすすめ"],
      ["link" => "osusume-fuyu.php", "img" => "img/fuyu.png", "text" => "冬におすすめ"],
      ["link" => "osusume-autdoa.php", "img" => "img/autodoa.jpg", "text" => "アウトドアにおすすめ"],
      ["link" => "osusume-supot.php", "img" => "img/supotu.png", "text" => "スポーツにおすすめ"],
      ["link" => "osusume-ame.php", "img" => "img/flower.jpg", "text" => "雨におすすめ"]
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

<?php
session_start();
if (empty($_SESSION['admin_login'])) {
    header('Location: admin_login.php');
    exit;
}
$admin_name = $_SESSION['admin_name'] ? $_SESSION['admin_name'] : '○○';

// データベース接続
require_once __DIR__ . '/../require/db-connect.php';
$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $price = $_POST["price"];
    $shipping_fee = $_POST["shipping_fee"];
    $stock = $_POST["stock"];
    $code = $_POST["code"];
    $brand = $_POST["brand"];
    $size = $_POST["size"];
    $category = $_POST["category"];
    $description = $_POST["description"];


    try {
        if (!empty($name) && !empty($price) && !empty($shipping_fee) && !empty($stock) && !empty($code)) {
            // Productテーブルに商品登録
            $stmt = $pdo->prepare("
                INSERT INTO Product
                (product_name, product_code, brand, size, price, stock, shipping_fee, category, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $code, $brand, $size, $price, $stock, $shipping_fee, $category, $description]);
            $product_id = $pdo->lastInsertId(); // 登録された商品のIDを取得

            // 複数画像アップロード処理
            if (!empty($_FILES["images"]["name"][0])) {
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
                    if ($_FILES["images"]["error"][$key] === UPLOAD_ERR_OK) {
                        $file_name = uniqid() . "_" . basename($_FILES["images"]["name"][$key]);
                        $target_path = $upload_dir . $file_name;
                        move_uploaded_file($tmp_name, $target_path);

                        // ProductImageテーブルに画像を登録
                        $stmt = $pdo->prepare("INSERT INTO ProductImage (product_id, image_url) VALUES (?, ?)");
                        $stmt->execute([$product_id, $target_path]);
                    }
                }
            }

            $message = "商品を登録しました！";
        } else {
            $message = "未入力の項目があります。";
        }
    } catch (PDOException $e) {
        $message = "エラー: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>商品登録 - Calçar</title>
  <link rel="stylesheet" href="../admincss/admin_product.css">
</head>
<body>
  <header class="header">
        <div class="logo">Calçar</div>
        <nav class="nav">
            <a href="admin_product.php">商品登録</a>
            <a href="admin_product_edit.php" class="active">商品管理</a>
            <a href="admin_user.php">ユーザー削除</a>
            <a href="admin_sales.php">売上管理</a>
        </nav>
        <div class="welcome">ようこそ！<?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?>さん！</div>
    </header>

  <main class="main">
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <form class="product-form" method="POST" enctype="multipart/form-data">

  <div class="form-grid">

    <div class="form-group">
      <label>商品名</label>
      <input type="text" name="name" required>
    </div>

    <div class="form-group">
      <label>価格</label>
      <input type="number" name="price" required>
    </div>

    <div class="form-group">
      <label>送料</label>
      <input type="number" name="shipping_fee" required>
    </div>

    <div class="form-group">
      <label>在庫数</label>
      <input type="number" name="stock" required>
    </div>

    <div class="form-group">
      <label>商品コード</label>
      <input type="text" name="code" required>
    </div>

    <div class="form-group">
      <label>ブランド</label>
      <input type="text" name="brand">
    </div>

    <div class="form-group">
      <label>サイズ</label>
      <input type="text" name="size">
    </div>

    <div class="form-group">
      <label>カテゴリー</label>
      <input type="text" name="category" required>
    </div>

    <div class="form-group full">
      <label>商品の説明</label>
      <textarea name="description"></textarea>
    </div>
      <div class="upload-box">
        <input type="file" name="images" id="imageInput" accept="image/*" class="full-width" multiple>
        <i class="fa-solid fa-camera"></i>
        <p class="upload-text">画像をアップロード</p>
        <div id="previewArea"></div>
      </div>

  </div>

  <div class="btn-wrap">
    <button type="submit">登録</button>
  </div>

</form>


      <div class="info-section">
        <div class="code-info">
          <div class="left">
            <h3>商品コード定義</h3>
            <p>カテゴリー カラー サイズ 素材</p>
            <p><strong>例）1-BLA-26A-LEA</strong></p>
            <p>　　春　黒　26.5　レザー</p>
          </div>
          <div class="right">
            <p>サイズの.5のサイズは〇〇Aと表記</p>
            <p>カテゴリ、カラー、素材は<br>頭文字3文字を大文字表記</p>
          </div>
        </div>
      </div>
  </main>
  <script src="../js/admin_product.js"></script>
</body>
</html>
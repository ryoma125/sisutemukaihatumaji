<?php
session_start();
require_once '../require/db-connect.php';
require '../require/navigation.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 商品ID取得（GETでサイズ変更時に切替）
    $id = isset($_GET['id']) ? intval($_GET['id']) : 1;

    // メイン商品取得
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
   product_code を分解
--------------------------------------------------- */
$codeParts = explode("-", $product["product_code"]);
$itemNo     = $codeParts[0] ?? "";
$colorCode  = $codeParts[1] ?? "";
$sizeCode   = $codeParts[2] ?? "";
$materialCode = $codeParts[3] ?? "";

/* サイズ変換関数 */
function convertSize($raw)
{
    if (strtoupper(substr($raw, -1)) === "A") {
        return floatval(substr($raw, 0, -1)) + 0.5;
    }
    return floatval($raw);
}

/* 同シリーズサイズ一覧取得 */
$pattern = $itemNo . "-" . $colorCode . "-%-" . $materialCode;

$stmt2 = $pdo->prepare("
    SELECT product_id, size, product_code
    FROM Product
    WHERE product_code LIKE :code
    ORDER BY size
");
$stmt2->bindValue(':code', $pattern);
$stmt2->execute();
$sizeItems = $stmt2->fetchAll(PDO::FETCH_ASSOC);
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

    <!-- 商品画像 -->
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
        <!-- サイズ選択 -->
        <div class="size-area">
          <!-- GETでid送信してページ再読み込み -->
          <form action="product_detail.php" method="GET">
            <label for="size">サイズ選択</label>
            <select id="size" name="id" onchange="this.form.submit()">
              <?php foreach ($sizeItems as $item): ?>
                <option value="<?= $item['product_id'] ?>"
                  <?= ($item['product_id'] == $product['product_id']) ? 'selected' : '' ?>>
                  <?= convertSize($item['size']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>

          <!-- カートボタンは元のまま -->
          <form action="cart_insert.php" method="POST">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <button type="submit" class="cart-btn">カートに入れる</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- 右側：商品説明・詳細 -->
  <div class="right">
    <div class="description">
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    </div>

    <?php
    $colorList = [
      "BLA" => "黒","WHT" => "白","RED" => "赤","BLU" => "青",
      "GRN" => "緑","BRN" => "茶","BEI" => "ベージュ","GRY" => "グレー","YEL" => "黄"
    ];
    $materialList = [
      "LEA" => "レザー","FAB" => "ファブリック","MSH" => "メッシュ","SYN" => "合成皮革"
    ];
    $colorName = $colorList[$colorCode] ?? "ー";
    $materialName = $materialList[$materialCode] ?? "ー";
    ?>

    <table class="detail-table">
      <tr><th>ブランド</th><td><?= htmlspecialchars($product['brand']) ?></td></tr>
      <tr><th>カテゴリ</th><td><?= htmlspecialchars($product['category']) ?></td></tr>
      <tr><th>カラー</th><td><?= htmlspecialchars($colorName) ?></td></tr>
      <tr><th>素材</th><td><?= htmlspecialchars($materialName) ?></td></tr>
      <!-- 選択されたサイズに応じた商品コードに自動で切り替わる -->
      <tr><th>商品コード</th><td><?= htmlspecialchars($product['product_code']) ?></td></tr>
    </table>
  </div>
</main>


</html>

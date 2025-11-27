<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../システム開発/require/db-connect.php';

// URLパラメータからサイズ取得（デフォルトは27）
$size_param = $_GET['size'] ?? '27';

// 内部コードに変換（27 → 27）
$code = str_replace('.5', 'A', $size_param); // 27 はそのまま

// 表示用サイズは元の値（27）
$display_size = $size_param;

// Productテーブルから該当サイズの商品取得（size列で判別）
try {
    $pdo = connect(); // db_connect.phpのconnect()関数を使用

    $stmt = $pdo->prepare("SELECT * FROM Product WHERE size = :size");
    $stmt->bindValue(':size', $code, PDO::PARAM_STR);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    exit('DBエラー: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calçar | <?= htmlspecialchars($display_size, ENT_QUOTES, 'UTF-8') ?></title>
    
    <link rel="stylesheet" href="../システム開発/require.css/navigation.css">
    <link rel="stylesheet" href="../shoze_css/shoze_size.css">
</head>
<body>

<?php require '../システム開発/require/navigation.php'; ?>

<main>
    <h1><?= htmlspecialchars($display_size, ENT_QUOTES, 'UTF-8') ?> サイズ</h1>

    <div class="product-grid" id="productGrid">
        <?php if(count($products) > 0): ?>
            <?php foreach($products as $p): ?>
                <div class="product">
                    <!-- 商品画像をクリックで詳細ページへ -->
                    <a href="product_detail.php?id=<?= htmlspecialchars($p['product_id'], ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= htmlspecialchars($p['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?>">
                    </a>

                    <!-- 商品名もクリックできる -->
                    <a href="product_detail.php?id=<?= htmlspecialchars($p['product_id'], ENT_QUOTES, 'UTF-8') ?>" class="product-name-link">
                        <div class="product-brand"><?= htmlspecialchars($p['brand'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="product-name"><?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?></div>
                    </a>

                    <div class="product-price">¥<?= number_format($p['price']) ?></div>

                    <?php if($p['stock'] > 0): ?>
                        <div class="product-stock">在庫あり (<?= intval($p['stock']) ?>個)</div>
                    <?php else: ?>
                        <div class="product-stock out-of-stock">在庫切れ</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-products">このサイズの商品はまだありません。</p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; 2024 Calçar. All rights reserved.</p>
</footer>

</body>
</html>

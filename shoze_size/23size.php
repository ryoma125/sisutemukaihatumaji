<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../システム開発/require.php/db_connect.php';

// ★ 23cm 固定
$size_param = '23';   // 表示用
$code = '23';         // Product.size に保存されている内部コード

try {
    $pdo = connect();

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
    <title>Calçar | <?= htmlspecialchars($size_param, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="../shoze_css/shoze_size.css">
</head>
<body>

<header>
    <a href="../index.html" class="logo-link">
        <div class="logo">Calçar</div>
    </a>
    <nav class="nav">
        <a href="../index.html">HOME</a>
        <a href="#">SHOP</a>
        <a href="#">ABOUT</a>
    </nav>
</header>

<main>
    <h1><?= htmlspecialchars($size_param, ENT_QUOTES, 'UTF-8') ?> サイズ</h1>

    <div class="product-grid" id="productGrid">
        <?php if(count($products) > 0): ?>
            <?php foreach($products as $p): ?>
                <div class="product">
                    <img src="<?= htmlspecialchars($p['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="product-brand"><?= htmlspecialchars($p['brand'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="product-name"><?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?></div>
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

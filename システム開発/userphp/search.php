<?php
session_start();
require_once "../require/db-connect.php";

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("DB接続失敗: " . $e->getMessage());
}

// 🔍 キーワード取得
$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";

// スペース区切りで複数検索
$keywords = preg_split('/[\s　]+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);

$results = [];

if (!empty($keywords)) {

    $sql = "SELECT * FROM Product WHERE ";
    $conditions = [];
    $params = [];

    foreach ($keywords as $i => $kw) {
        $conditions[] = "(
            product_name LIKE :kw$i OR
            product_code LIKE :kw$i OR
            brand LIKE :kw$i OR
            size LIKE :kw$i
        )";
        $params[":kw$i"] = "%$kw%";
    }

    $sql .= implode(" AND ", $conditions);

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果 | Calçar</title>
    <script src="../js/search_suggest.js"></script>

    <!-- 🔽 カードデザイン用CSS -->
    <style>
        body {
            font-family: "Arial", sans-serif;
        }

        .search-title {
            margin: 20px 0;
            font-size: 24px;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            padding: 20px;
        }

        .product-card {
            width: 220px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            transition: 0.2s;
            background: #fff;
        }

        .product-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transform: translateY(-3px);
        }

        .product-card img {
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
        }

        .product-name {
            font-size: 16px;
            margin: 8px 0;
            font-weight: bold;
        }

        .product-info {
            font-size: 14px;
            color: #444;
        }
    </style>
</head>
<body>

<?php include "header.php"; ?>

<h2 class="search-title">「<?= htmlspecialchars($keyword) ?>」の検索結果</h2>

<?php if ($keyword === ""): ?>

    <p>キーワードを入力してください。</p>

<?php elseif (empty($results)): ?>

    <p>該当する商品がありません。</p>

<?php else: ?>

    <div class="product-list">

    <?php foreach ($results as $item): ?>
        <div class="product-card">

            <!-- 🔽 画像クリックで商品詳細へ -->
            <a href="product_detail.php?id=<?= $item['product_id'] ?>">
                <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="商品画像">
                <?php else: ?>
                    <img src="../img/noimage.png" alt="画像なし">
                <?php endif; ?>
            </a>

            <!-- 🔽 商品名もリンクにする -->
            <a href="product_detail.php?id=<?= $item['product_id'] ?>" class="product-name">
                <?= htmlspecialchars($item['product_name']) ?>
            </a>

            <div class="product-info">
                ブランド：<?= htmlspecialchars($item['brand']) ?><br>
                商品コード：<?= htmlspecialchars($item['product_code']) ?><br>
                サイズ：<?= htmlspecialchars($item['size']) ?><br>
                価格：￥<?= number_format($item['price']) ?>
            </div>

        </div>
    <?php endforeach; ?>

    </div>

<?php endif; ?>

</body>
</html>

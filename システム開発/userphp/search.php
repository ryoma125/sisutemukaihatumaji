<?php
session_start();
require_once "../require/db-connect.php";  // ← パスはこれでOK

try {
    // db-connect.php に合わせて接続
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("DB接続失敗: " . $e->getMessage());
}

// 🔍 キーワード取得
$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";

// スペース区切りで複数キーワード対応
$keywords = preg_split('/[\s　]+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);

$results = [];

if (!empty($keywords)) {

    // 🔍 検索SQL作成
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

    // AND で複数条件を繋げる
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
    <script src="/js/search_suggest.js"></script>
    <style>
        .product-item { margin-bottom: 20px; }
        .product-item img { cursor: pointer; width: 180px; border-radius: 5px; }
    </style>
</head>
<body>

<?php include "header.php"; ?>

<h2>「<?= htmlspecialchars($keyword) ?>」の検索結果</h2>

<?php if ($keyword === ""): ?>

    <p>キーワードを入力してください。</p>

<?php elseif (empty($results)): ?>

    <p>該当する商品がありません。</p>

<?php else: ?>

    <?php foreach ($results as $item): ?>
        <div class="product-item">

            <!-- 画像クリックで商品詳細へ遷移 -->
            <a href="product_detail.php?id=<?= $item['product_id'] ?>">
                <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="">
                <?php else: ?>
                    <img src="/noimage.png" alt="画像なし">
                <?php endif; ?>
            </a>

            <p><strong><?= htmlspecialchars($item['product_name']) ?></strong></p>
            <p>ブランド：<?= htmlspecialchars($item['brand']) ?></p>
            <p>商品コード：<?= htmlspecialchars($item['product_code']) ?></p>
            <p>サイズ：<?= htmlspecialchars($item['size']) ?></p>
            <p>価格：<?= number_format($item['price']) ?> 円</p>

        </div>
    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>

<?php
session_start();
require_once "../require/db-connect.php";
require_once "../require/navigation.php";

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("DB接続失敗: " . $e->getMessage());
}

// 🔍 キーワード取得
$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";
$keywords = preg_split('/[\s　]+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);

//==========================================
// 🔽 マッピング辞書（ひらがな変換なし）
//==========================================

// ブランド
$brandMap = [
    "ナイキ" => "Nike",
    "アディダス" => "Adidas",
    "ニューバランス" => "New Balance",
    "プーマ" => "Puma",
    "リーボック" => "Reebok",
    "アシックス" => "Asics",
    "ミズノ" => "Mizuno",
    "コンバース" => "Converse",
    "バンズ" => "Vans",
    "ドクターマーチン" => "Dr.Martens",
    "レッドウィング" => "Red Wing",
    "ティンバーランド" => "Timberland",
    "クラークス" => "Clarks",
    "アグ" => "UGG",
    "テバ" => "Teva",
    "モンクレール" => "MONCLER",
    "チャコ" => "Chaco",
    "リーガル" => "REGAL",
    "ハルタ" => "Haruta",
    "テクシーリュクス" => "Texcy Luxe"
];

// 色
$colorMap = [
    "黒" => "BLA", "クロ" => "BLA",
    "白" => "WHT",
    "赤" => "RED",
    "青" => "BLU",
    "緑" => "GRN",
    "茶" => "BRN",
    "グレー" => "GRY",
    "ベージュ" => "BEI",
    "黄" => "YEL",
];

// 素材
$materialMap = [
    "レザー" => "LEA",
    "合成皮革" => "SYN",
    "メッシュ" => "MSH",
    "ファブリック" => "FAB"
];

// サイズゆらぎ（23.5 → 23A）
function normalizeSize($kw)
{
    $kw = mb_convert_kana($kw, "n");
    $kw = str_replace(["cm", "ｃｍ", "CM", "㎝", "センチ", "せんち"], "", $kw);
    $kw = trim($kw);

    if (!preg_match('/^[0-9.]+$/', $kw)) {
        return null;
    }

    if (strpos($kw, ".") !== false) {
        list($main, $dec) = explode(".", $kw);
        return ($dec == "5") ? $main . "A" : $main;
    }

    return $kw;
}

//----------------------------------------------
// 🔽 属性判定
//----------------------------------------------
$cond_brand = null;
$cond_color = null;
$cond_material = null;
$cond_size = null;
$others = [];

foreach ($keywords as $kw) {

    // サイズ判定
    $size = normalizeSize($kw);
    if ($size !== null) {
        $cond_size = $size;
        continue;
    }

    // ブランド
    foreach ($brandMap as $jp => $en) {
        if (strpos($kw, $jp) !== false) {
            $cond_brand = $en;
            continue 2;
        }
    }

    // 色
    foreach ($colorMap as $jp => $code) {
        if (strpos($kw, $jp) !== false) {
            $cond_color = $code;
            continue 2;
        }
    }

    // 素材
    foreach ($materialMap as $jp => $code) {
        if (strpos($kw, $jp) !== false) {
            $cond_material = $code;
            continue 2;
        }
    }

    // その他
    $others[] = $kw;
}

//----------------------------------------------
// 🔽 SQL 組み立て（COLLATE を明示）
//----------------------------------------------
$sql = "
SELECT p.*
FROM Product p
WHERE 1
";

$params = [];

// ブランド（完全一致）
if ($cond_brand !== null) {
    $sql .= " AND p.brand COLLATE utf8mb4_general_ci = :brand ";
    $params[':brand'] = $cond_brand;
}

// 色（product_code内）
if ($cond_color !== null) {
    $sql .= " AND p.product_code COLLATE utf8mb4_general_ci LIKE :color ";
    $params[':color'] = "%$cond_color%";
}

// 素材（product_code内）
if ($cond_material !== null) {
    $sql .= " AND p.product_code COLLATE utf8mb4_general_ci LIKE :mat ";
    $params[':mat'] = "%$cond_material%";
}

// サイズ（完全一致）
if ($cond_size !== null) {
    $sql .= " AND p.size COLLATE utf8mb4_general_ci = :size ";
    $params[':size'] = $cond_size;
}

// フリーワード
foreach ($others as $i => $word) {
    $sql .= " AND (
        p.product_name COLLATE utf8mb4_general_ci LIKE :w$i
        OR
        p.description COLLATE utf8mb4_general_ci LIKE :w$i
    )";
    $params[":w$i"] = "%$word%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>検索結果 | Calçar</title>

<style>
body { font-family: Arial, sans-serif; }
.product-list { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; }
.product-card { width: 200px; background: #fff; border-radius: 10px; padding: 10px; border: 1px solid #ddd; }
.product-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; }
.product-name { font-size: 15px; font-weight: bold; margin-top: 8px; color: #222; }
</style>

</head>
<body>

<?php include "header.php"; ?>

<h2 class="search-title">「<?= htmlspecialchars($keyword) ?>」の検索結果</h2>

<?php if (empty($results)): ?>
<p>該当する商品がありません。</p>
<?php else: ?>
<div class="product-list">
<?php foreach ($results as $item): ?>
    <div class="product-card">
        <a href="product_detail.php?id=<?= $item['product_id'] ?>">
            <img src="<?= htmlspecialchars($item['image_url']) ?>">
        </a>

        <a class="product-name" href="product_detail.php?id=<?= $item['product_id'] ?>">
            <?= htmlspecialchars($item['product_name']) ?>
        </a>

        <div class="product-info">
            ブランド：<?= htmlspecialchars($item['brand']) ?><br>
            サイズ：<?= htmlspecialchars($item['size']) ?><br>
            コード：<?= htmlspecialchars($item['product_code']) ?><br>
            ¥<?= number_format($item['price']) ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>

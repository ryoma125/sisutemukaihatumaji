<?php
session_start();
require_once "../require/db-connect.php";
require_once "../require/navigation.php";

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    exit("DB接続失敗: " . $e->getMessage());
}

// ============================
// 🔍 キーワード取得
// ============================
$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";

// ★ひらがな / 半角カナ / 全角カナ を統一（カタカナに）
$keyword = mb_convert_kana($keyword, 'KVC');

// スペースで分割
$keywords = preg_split('/[\s　]+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);


// ============================
// 🔤 正規化（カタカナ → ひらがな相当のキーに）
//   ※ブランド・色・素材判定用
// ============================
function normalizeKanaFull($str) {
    // 小文字・半角 → 全角 / ひらがなに寄せる感じの変換（元コードそのまま）
    $str = mb_convert_kana(mb_strtolower($str), "cHV");

    // 長音の処理用の簡易マップ
    $map = [
        "あ"=>"あ","い"=>"い","う"=>"う","え"=>"え","お"=>"お",
        "か"=>"あ","き"=>"い","く"=>"う","け"=>"え","こ"=>"お",
        "さ"=>"あ","し"=>"い","す"=>"う","せ"=>"え","そ"=>"お",
        "た"=>"あ","ち"=>"い","つ"=>"う","て"=>"え","と"=>"お",
        "な"=>"あ","に"=>"い","ぬ"=>"う","ね"=>"え","の"=>"お",
        "は"=>"あ","ひ"=>"い","ふ"=>"う","へ"=>"え","ほ"=>"お",
        "ま"=>"あ","み"=>"い","む"=>"う","め"=>"え","も"=>"お",
        "や"=>"あ","ゆ"=>"う","よ"=>"お",
        "ら"=>"あ","り"=>"い","る"=>"う","れ"=>"え","ろ"=>"お",
        "わ"=>"あ","を"=>"お",
    ];

    $result = "";
    $len = mb_strlen($str);

    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($str, $i, 1);

        // 長音「ー」は直前の母音に変換
        if ($ch === "ー" && $i > 0) {
            $prev = mb_substr($result, -1);
            $result .= $map[$prev] ?? $prev;
        } else {
            $result .= $ch;
        }
    }

    return $result;
}


// ============================
// 🏷 DBからブランド一覧を取得（自動対応）
// ============================
$sqlBrand = "SELECT DISTINCT brand FROM Product";
$stmtBrand = $pdo->query($sqlBrand);
$dbBrands = $stmtBrand->fetchAll(PDO::FETCH_COLUMN);

// ひらがな的キー → 実際のブランド名 のマッピング
$brandMap = [];

foreach ($dbBrands as $brand) {
    $brandMap[ normalizeKanaFull($brand) ] = $brand;
}


// ============================
// 🎨 色・素材 辞書
// ============================
$colorMap = [
    "くろ" => "BLA", "黒" => "BLA",
    "しろ" => "WHT", "白" => "WHT",
    "あか" => "RED", "赤" => "RED",
    "あお" => "BLU", "青" => "BLU",
    "みどり" => "GRN", "緑" => "GRN",
    "きいろ" => "YEL", "黄" => "YEL",
    "ちゃ" => "BRN", "茶" => "BRN",
];

$materialMap = [
    "れざー" => "LEA", "レザー" => "LEA",
    "ごうせいひかく" => "SYN", "合成皮革" => "SYN",
    "めっしゅ" => "MSH", "メッシュ" => "MSH",
    "ふぁぶりっく" => "FAB", "ファブリック" => "FAB",
];


// ============================
// 📏 サイズゆらぎ（22.5 → 22A）
// ============================
function normalizeSize($kw) {
    // 全角数字 → 半角数字
    $kw = mb_convert_kana($kw, "n");
    // 「cm」「センチ」などを除去
    $kw = str_replace(["cm","㎝","センチ"], "", $kw);

    if (!preg_match('/^[0-9.]+$/', $kw)) return null;

    if (strpos($kw, ".") !== false) {
        list($m,$d) = explode(".", $kw);
        return ($d=="5") ? $m."A" : $m;
    }
    return $kw;
}


// ============================
// 🔎 キーワード分類
// ============================
$cond_brand    = null;
$cond_color    = null;
$cond_material = null;
$cond_size     = null;
$others        = [];

foreach ($keywords as $kw) {

    // ひらがな的な正規化キー（ブランド・色・素材判定に使う）
    $h = normalizeKanaFull($kw);

    // サイズ
    $s = normalizeSize($kw);
    if ($s !== null) {
        $cond_size = $s;
        continue;
    }

    // ブランド
    if (isset($brandMap[$h])) {
        $cond_brand = $brandMap[$h];
        continue;
    }

    // 色
    if (isset($colorMap[$h])) {
        $cond_color = $colorMap[$h];
        continue;
    }

    // 素材
    if (isset($materialMap[$h])) {
        $cond_material = $materialMap[$h];
        continue;
    }

    // どれにも当てはまらない → フリーワード
    $others[] = $kw;
}


// ============================
// 🗂 SQL（重複商品をまとめて表示）
// ============================
$sql = "
SELECT p.*
FROM Product p
INNER JOIN (
    SELECT product_name, MIN(product_id) AS min_id
    FROM Product
    GROUP BY product_name
) AS uniq
ON uniq.min_id = p.product_id
WHERE 1
";

$params = [];

// ブランド条件
if ($cond_brand !== null) {
    $sql .= " AND p.brand = :brand ";
    $params[":brand"] = $cond_brand;
}

// 色（product_code に "BLA" などが入っている想定）
if ($cond_color !== null) {
    $sql .= " AND p.product_code LIKE :color ";
    $params[":color"] = "%$cond_color%";
}

// 素材（product_code に "LEA" などが入っている想定）
if ($cond_material !== null) {
    $sql .= " AND p.product_code LIKE :mat ";
    $params[":mat"] = "%$cond_material%";
}

// サイズ
if ($cond_size !== null) {
    $sql .= " AND p.size = :size ";
    $params[":size"] = $cond_size;
}

// フリーワード（商品名・説明・ブランド名にLIKE）
foreach ($others as $i => $word) {

    // 念のためここでもカタカナ統一（ひらがな入力などに対応）
    $word = mb_convert_kana($word, 'KVC');

    $sql .= " AND (p.product_name LIKE :w$i OR p.description LIKE :w$i OR p.brand LIKE :w$i)";
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
.search-title { margin:20px; font-size:22px; }
.product-list { display:flex; flex-wrap:wrap; gap:20px; padding:20px; }
.product-card { width:200px; border:1px solid #ddd; background:#fff; border-radius:10px; padding:10px; }
.product-card img { width:100%; height:150px; object-fit:cover; border-radius:6px; }
.product-name { font-weight:bold; margin-top:6px; display:block; }
</style>

</head>
<body>

<?php include "header.php"; ?>

<h2 class="search-title">「<?= htmlspecialchars($keyword) ?>」の検索結果</h2>

<?php if (empty($results)): ?>
    <p>該当商品なし</p>

<?php else: ?>
    <div class="product-list">
        <?php foreach ($results as $p): ?>
            <div class="product-card">
                <a href="product_detail.php?id=<?= $p['product_id'] ?>">
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="">
                </a>
                <span class="product-name"><?= htmlspecialchars($p['product_name']) ?></span>
                ブランド：<?= htmlspecialchars($p['brand']) ?><br>
                サイズ：<?= htmlspecialchars($p['size']) ?><br>
                価格：￥<?= number_format($p['price']) ?><br>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>

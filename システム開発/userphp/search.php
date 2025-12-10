<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../require/db-connect.php";
require_once "../require/navigation.php";

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    exit("DB接続失敗: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/* ----------------------------------------------------
 * 共通：カタカナ→ひらがな
 * ---------------------------------------------------- */
function toHiragana($str) {
    $str = mb_convert_kana($str, "KV");
    return mb_convert_kana($str, "c");
}

/* ----------------------------------------------------
 * サイズゆらぎ（23 → 23, 23.5 → 23A）
 * ---------------------------------------------------- */
function normalizeSize($kw) {
    $kw = mb_convert_kana($kw, "n");
    $kw = str_replace(["cm","㎝","センチ","せんち"], "", $kw);
    $kw = trim($kw);

    if ($kw === "") return null;

    if (!preg_match('/^[0-9]+(\.[0-9])?$/', $kw)) return null;

    if (strpos($kw, ".") !== false) {
        list($m,$d) = explode(".", $kw);
        return ($d === "5") ? $m."A" : $m;
    }
    return $kw;
}

/* ----------------------------------------------------
 * 23 / 23A → 23cm / 23.5cm へ変換（表示用）
 * ---------------------------------------------------- */
function displaySize($size) {
    if (preg_match('/^[0-9]+A$/', $size)) {
        return str_replace("A", ".5", $size) . "cm";
    }
    return $size . "cm";
}

/* ----------------------------------------------------
 * ブランド判定
 * ---------------------------------------------------- */
function detectBrand($kw) {
    $h = toHiragana($kw);
    $map = [
        "ないき" => "Nike",
        "あでぃだす" => "Adidas",
        "こんばーす" => "Converse",
        "ばんず" => "Vans",
        "みずの" => "Mizuno",
        "れっどうぃんぐ" => "Red Wing",
        "はるた" => "Haruta",
        "てば" => "Teva",
        "ちゃこ" => "Chaco",
        "くらーくす" => "Clarks",
        "あぐ" => "UGG",
        "もんくれーる" => "MONCLER",
    ];
    foreach ($map as $hira => $brand) {
        if (mb_strpos($h, $hira) !== false || mb_strpos($kw, $hira) !== false) {
            return $brand;
        }
    }
    return null;
}

/* ----------------------------------------------------
 * カラー判定
 * ---------------------------------------------------- */
function detectColor($kw) {
    $h = toHiragana($kw);
    $map = [
        "くろ" => "BLA", "黒" => "BLA", "ブラック" => "BLA",
        "しろ" => "WHT", "白" => "WHT", "ホワイト" => "WHT",
        "あか" => "RED", "赤" => "RED", "レッド" => "RED",
        "あお" => "BLU", "青" => "BLU", "ブルー" => "BLU",
        "みどり" => "GRN", "緑" => "GRN", "グリーン" => "GRN",
        "ちゃ" => "BRN", "茶" => "BRN", "ブラウン" => "BRN",
        "きいろ" => "YEL", "黄" => "YEL", "イエロー" => "YEL",
        "グレー" => "GRY",
        "ベージュ" => "BEI",
    ];
    foreach ($map as $key => $val) {
        if (mb_strpos($h, toHiragana($key)) !== false || mb_strpos($kw, $key) !== false) {
            return $val;
        }
    }
    return null;
}

/* ----------------------------------------------------
 * 素材判定
 * ---------------------------------------------------- */
function detectMaterial($kw) {
    $h = toHiragana($kw);
    if (mb_strpos($h, "れざ") !== false || mb_strpos($kw, "レザー") !== false) return "LEA";
    if (mb_strpos($h, "ごうせいひかく") !== false || mb_strpos($kw, "合成皮革") !== false) return "SYN";
    if (mb_strpos($h, "めっしゅ") !== false || mb_strpos($kw, "メッシュ") !== false) return "MSH";
    if (mb_strpos($kw, "ファブリック") !== false) return "FAB";
    return null;
}

/* ----------------------------------------------------
 * カテゴリ判定
 * ---------------------------------------------------- */
function detectCategory($kw) {
    $h = toHiragana($kw);
    if (mb_strpos($kw, "スニーカー") !== false || mb_strpos($h, "すにーかー") !== false) return "スニーカー";
    if (mb_strpos($kw, "ブーツ")     !== false || mb_strpos($h, "ぶーつ") !== false)     return "ブーツ";
    if (mb_strpos($kw, "サンダル")   !== false || mb_strpos($h, "さんだる") !== false)  return "サンダル";
    if (mb_strpos($kw, "スポーツ")   !== false || mb_strpos($kw, "ランニング") !== false) return "スポーツ";
    return null;
}

/* ----------------------------------------------------
 * キーワード解析
 * ---------------------------------------------------- */
$keyword  = isset($_GET['q']) ? trim($_GET['q']) : "";
$keywords = preg_split('/[\s　]+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY);

$cond_brand = $cond_color = $cond_material = $cond_size = $cond_category = null;
$others = [];

foreach ($keywords as $kw) {

    if (($s = normalizeSize($kw)) !== null) { $cond_size = $s; continue; }
    if ($cond_brand === null   && ($b = detectBrand($kw))    !== null) { $cond_brand = $b; continue; }
    if ($cond_color === null   && ($c = detectColor($kw))    !== null) { $cond_color = $c; continue; }
    if ($cond_material === null&& ($m = detectMaterial($kw)) !== null) { $cond_material = $m; continue; }
    if ($cond_category === null&& ($cat = detectCategory($kw)) !== null) { $cond_category = $cat; continue; }

    $others[] = $kw;
}

/* ----------------------------------------------------
 * SQL
 * ---------------------------------------------------- */
$sql = "SELECT * FROM Product WHERE 1";
$params = [];

if ($cond_brand !== null)    { $sql .= " AND brand = :brand";      $params[':brand'] = $cond_brand; }
if ($cond_color !== null)    { $sql .= " AND product_code LIKE :color"; $params[':color'] = "%{$cond_color}%"; }
if ($cond_material !== null) { $sql .= " AND product_code LIKE :mat";   $params[':mat'] = "%{$cond_material}%"; }
if ($cond_size !== null)     { $sql .= " AND size = :size";            $params[':size'] = $cond_size; }
if ($cond_category !== null) { $sql .= " AND category = :cat";         $params[':cat'] = $cond_category; }

foreach ($others as $i => $w) {
    $sql .= " AND (product_name LIKE :w{$i}
              OR description LIKE :w{$i}
              OR brand LIKE :w{$i}
              OR category LIKE :w{$i})";
    $params[":w{$i}"] = "%{$w}%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ----------------------------------------------------
 * 商品名ごとに1件だけ
 * ---------------------------------------------------- */
$unique = [];
$names = [];
foreach ($results as $row) {
    if (!in_array($row['product_name'], $names)) {
        $unique[] = $row;
        $names[] = $row['product_name'];
    }
}
$results = $unique;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>検索結果 | Calçar</title>
<link rel="stylesheet" href="/2025/GitHub/sisutemukaihatumaji/システム開発/require.css/navigation.css">

<style>
body {
    background: #f5f7fa; /* 写真が際立つ淡い背景 */
}

/* 見出し */
.result-title {
    font-size: 26px;
    font-weight: bold;
    margin: 30px 60px 10px;
}

/* 商品一覧（右側空白なし） */
.product-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 24px;
    padding: 0 60px 40px;
}

/* カード全体リンク化 */
.product-card {
    display: block;
    text-decoration: none;
    color: inherit;
    border: 1px solid #ddd;
    border-radius: 14px;
    padding: 12px;
    background: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transition: transform .2s, box-shadow .2s;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}

/* 画像統一 */
.product-card img {
    width: 100%;
    height: 260px;
    object-fit: cover;
    border-radius: 10px;
}

/* テキスト */
.product-name {
    font-weight: bold;
    margin-top: 10px;
    font-size: 16px;
}

.product-brand,
.product-size,
.product-price {
    font-size: 14px;
    margin: 4px 0;
}
</style>
</head>

<body>

<h2 class="result-title">「<?= htmlspecialchars($keyword) ?>」の検索結果</h2>

<?php if (empty($results)): ?>
    <p style="margin-left:60px;">該当商品なし</p>
<?php else: ?>
<div class="product-list">
    <?php foreach ($results as $p): ?>
        <a class="product-card" href="product_detail.php?id=<?= $p['product_id'] ?>">

            <img src="<?= $p['image_url'] ?>" alt="">

            <div class="product-name"><?= $p['product_name'] ?></div>

            <div class="product-brand">
                ブランド：<?= $p['brand'] ?>
            </div>

            <div class="product-size">
                サイズ：<?= displaySize($p['size']) ?>
            </div>

            <div class="product-price">
                価格：¥<?= number_format($p['price']) ?>
            </div>

        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>

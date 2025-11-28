<?php
header("Content-Type: application/json; charset=UTF-8");

// DB接続
require "../require/db-connect.php";  // ← あなたの環境に合わせたパス

try {
    $pdo = new PDO(
        'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8',
        USER,
        PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo json_encode(["error" => "DB接続失敗: " . $e->getMessage()]);
    exit;
}

// 入力キーワード取得
$q = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($q === "") {
    echo json_encode([]);
    exit;
}

// SQL（商品名・ブランド・サイズ・コードを検索）
$sql = "
    SELECT product_id, product_name, brand, size
    FROM products
    WHERE product_name LIKE :q
       OR brand LIKE :q
       OR size LIKE :q
       OR product_code LIKE :q
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":q", "%" . $q . "%", PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// JSONで返す
echo json_encode($data);
exit;

<?php
// DB接続
require "../require/db-connect.php";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    exit("DB接続失敗: " . $e->getMessage());
}

// キーワード取得
$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";

// キーワードをスペースで分割（複数検索用）
$keywords = preg_split('/[\s　]+/', $keyword);

$results = [];

if ($keyword !== "") {

    // SQLを組み立て
    $sql = "SELECT * FROM products WHERE ";
    $conditions = [];
    $params = [];

    foreach ($keywords as $i => $kw) {
        $conditions[] = "(product_name LIKE :kw$i
                     OR product_code LIKE :kw$i
                     OR brand LIKE :kw$i
                     OR size LIKE :kw$i)";
        $params[":kw$i"] = "%$kw%";
    }

    $sql .= implode(" AND ", $conditions);

    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

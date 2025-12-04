<?php
session_start();
require '../require/db-connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    die("ログインしていません");
}

$user_id = $_SESSION['user_id'];
$total_price = isset($_POST['total']) ? (int)$_POST['total'] : 0;
$payment = $_POST['payment_method'] ?? "未選択";

$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// トランザクション開始（重要）
$pdo->beginTransaction();

try {

    // ==========================
    // 1. Order（注文）を保存
    // ==========================
    $sql = "INSERT INTO `Order` (user_id, total_price, order_date)
            VALUES (?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $total_price]);

    // 新しく作られた order_id を取得
    $order_id = $pdo->lastInsertId();


    // ==========================
    // 2. カートの中身を取得
    // ==========================
    $sql = "SELECT product_id, quantity FROM Cart WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // ==========================
    // 3. OrderDetail に登録
    // ==========================
    $sql = "INSERT INTO OrderDetail (order_id, product_id, quantity)
            VALUES (?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    foreach ($cart_items as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity']]);
    }


    // ==========================
    // 4. カートを空にする
    // ==========================
    $sql = "DELETE FROM Cart WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);


    // 全てOK → コミット
    $pdo->commit();

} catch (Exception $e) {

    // 失敗したらロールバック
    $pdo->rollBack();
    die("注文処理に失敗しました: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購入完了</title>
    <link rel="stylesheet" href="../usercss/order_complete.css">
</head>
<body>
    <div class="complete-container">
        <h1>Calçar</h1>
        <h2>ご購入ありがとうございます。</h2>
        <p>到着まで今しばらくお待ちください。</p>

        <button class="confirm-btn" onclick="location.href='index.php'">ホーム画面に戻る</button>
    </div>
</body>
</html>

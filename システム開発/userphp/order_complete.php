<?php
session_start();
require '../require/db-connect.php';
$pdo = new PDO($connect, USER, PASS);

// ===== 注文処理などをここで実行 =====
// 例）$payment = $_POST['payment_method'];
//     $user = $_SESSION['user_name'] ?? 'ゲスト';
//     DB登録や注文履歴の保存などを行う
// =====================================

// 処理が終わったら下で完了画面を表示
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

        <!-- ホームに戻るボタン -->
        <button class="confirm-btn" onclick="location.href='index.php'">ホーム画面に戻る</button>
    </div>
</body>
</html>

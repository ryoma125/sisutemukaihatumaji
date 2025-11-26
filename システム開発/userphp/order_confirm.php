<?php session_start();
 require '../require.php/db-connect.php'; 
$total = isset($_POST['total']) ? (int)$_POST['total'] : 0;
$pdo = new PDO($connect, USER, PASS);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>購入確認</title>
    <link rel="stylesheet" href="../usercss/order_confirm.css">
</head>
<body>
<div class="modal">
    <h2>購入確定しますか？</h2>

    <img src="shoe.jpg" alt="商品画像" class="product-image">

    <form action="order_complete.php" method="post">
        <div class="info-box">
            <p><strong>名前</strong></p>
            <p><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'ゲスト'; ?></p>

            <p class="label">支払方法選択</p>
            <label><input type="radio" name="payment_method" value="クレジットカード" required> クレジットカード</label><br>
            <label><input type="radio" name="payment_method" value="PayPay"> PayPay</label><br>
            <label><input type="radio" name="payment_method" value="コンビニ払い"> コンビニ払い</label>
        </div>

        <div class="total">
            <p>合計金額</p>
            <p class="price">0000円</p>
        </div>

        <div class="buttons">
            <button type="submit" class="confirm-btn">確定</button>
            <button type="button" class="cancel-btn" onclick="history.back()">キャンセル</button>
        </div>
    </form>
</div>
</body>
</html>

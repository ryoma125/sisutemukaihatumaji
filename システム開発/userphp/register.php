<?php
// セッションを開始
session_start();

// データベース接続用の関数を読み込む
require_once '../require.php/db-connect.php';

// データベースに接続
$pdo = new PDO($connect, USER, PASS);

// フォームがPOSTで送信されたときの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // フォームから送信された値を取得
    $name = $_POST['name'];           // お名前
    $email = $_POST['email'];         // メールアドレス
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // パスワードをハッシュ化
    $address = $_POST['address'];     // 住所

    // Userテーブルに登録（delete_flagは0固定）
    $stmt = $pdo->prepare("
        INSERT INTO User (name, email, password, address, delete_flag)
        VALUES (?, ?, ?, ?, 0)
    ");

    $stmt->execute([$name, $email, $pass, $address]);

    // 登録後、ログイン画面へリダイレクト
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../usercss/register.css?v=<?php echo time(); ?>">
<title>新規登録 | Calçar</title>
<style>
/* 簡易CSS */
.zip-container { display: flex; align-items: center; gap: 5px; }
</style>
</head>
<body>
<div class="container">
  <h1>Calçar</h1>
  <h2>新規登録画面</h2>
  <form method="post">
    <label>お名前</label><br>
    <input type="text" name="name" required><br>

    <label>メールアドレス</label><br>
    <input type="email" name="email" required><br>

    <label>パスワード</label><br>
    <input type="password" name="password" required><br>

    <label>郵便番号</label><br>
    <div class="zip-container">
      <input type="text" id="zip" maxlength="8" placeholder="例：100-0001">
      <button type="button" id="zipBtn">検索</button>
    </div><br>

    <label>住所</label><br>
    <textarea name="address" id="address" rows="3" required></textarea><br>

    <button type="submit">登録</button>
  </form>

  <div class="login-link">
    <a href="login.php">ログイン画面へ</a>
  </div>
</div>

<script>
// 郵便番号検索機能（郵便番号APIを利用）
document.getElementById('zipBtn').addEventListener('click', function() {
    const zip = document.getElementById('zip').value.replace('-', '').trim();
    if(!zip.match(/^\d{7}$/)) {
        alert('郵便番号は7桁で入力してください');
        return;
    }

    // 郵便番号APIをFetchで呼び出す
    fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${zip}`)
    .then(response => response.json())
    .then(data => {
        if(data.status === 200 && data.results) {
            const result = data.results[0];
            const address = result.address1 + result.address2 + result.address3;
            document.getElementById('address').value = address;
        } else {
            alert('住所が見つかりませんでした');
        }
    })
    .catch(err => {
        console.error(err);
        alert('住所検索に失敗しました');
    });
});
</script>
</body>
</html>

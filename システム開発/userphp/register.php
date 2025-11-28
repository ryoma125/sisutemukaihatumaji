<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// データベース接続
require_once '../require/db-connect.php';
$pdo = connect();

// エラーメッセージ用変数
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力値取得・トリム
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $zip = trim($_POST['zip'] ?? '');

    // 簡易バリデーション
    if ($name === '' || $email === '' || $password === '' || $address === '') {
        $error = 'すべての項目を入力してください。';
    } else {
        // パスワードをハッシュ化
        $passHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // User テーブルに登録
            $stmt = $pdo->prepare("
                INSERT INTO User (name, email, password, address, zip, delete_flag)
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$name, $email, $passHash, $address, $zip]);

            // 登録成功 → ログイン画面へ
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {
            // エラー表示
            $error = '登録に失敗しました: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>新規登録 | Calçar</title>
<link rel="stylesheet" href="../usercss/register.css?v=<?php echo time(); ?>">
<style>
.zip-container { display: flex; align-items: center; gap: 5px; }
p.error { color:red; margin-bottom:20px; }
</style>
</head>
<body>
<div class="container">
  <h1>Calçar</h1>
  <h2>新規登録画面</h2>

  <?php if($error !== ''): ?>
    <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <label>お名前</label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br>

    <label>メールアドレス</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br>

    <label>パスワード</label><br>
    <input type="password" name="password" required><br>

    <label>郵便番号</label><br>
    <div class="zip-container">
      <input type="text" id="zip" name="zip" maxlength="8" placeholder="例：100-0001" value="<?php echo htmlspecialchars($zip ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <button type="button" id="zipBtn">検索</button>
    </div><br>

    <label>住所</label><br>
    <textarea name="address" id="address" rows="3" required><?php echo htmlspecialchars($address ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea><br>

    <button type="submit">登録</button>
  </form>

  <div class="login-link">
    <a href="login.php">ログイン画面へ</a>
  </div>
</div>

<script>
// 郵便番号検索機能
document.getElementById('zipBtn').addEventListener('click', function() {
    const zip = document.getElementById('zip').value.replace('-', '').trim();
    if(!zip.match(/^\d{7}$/)) {
        alert('郵便番号は7桁で入力してください');
        return;
    }
    fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${zip}`)
    .then(response => response.json())
    .then(data => {
        if(data.status === 200 && data.results) {
            const result = data.results[0];
            document.getElementById('address').value = result.address1 + result.address2 + result.address3;
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

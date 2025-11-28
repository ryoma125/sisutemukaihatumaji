<?php 
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
require '../require/db-connect.php';
$pdo = new PDO($connect, USER, PASS);


// POST が来た時のみログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass  = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $pass === '') {
        $error = "メールアドレスとパスワードを入力してください。";
    } else {
        try {
            $sql = "SELECT user_id, name, email, password FROM User WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($pass, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];

                // ★ ここを index.html に変更 ★
                header('Location: index.php');
                exit;

            } else {
                $error = "メールアドレスまたはパスワードが違います。";
            }
        } catch (Exception $e) {
            $error = "サーバーエラーが発生しました。時間を置いて再度お試しください。";
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="../usercss/login.css?v=<?php echo time(); ?>">
  <title>ログイン画面 | Calçar</title>
</head>
<body>
<h1>Calçar</h1>
<div class="container">
<h2>ログイン画面</h2>

<?php if (isset($error)): ?>
  <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="post" autocomplete="off">
  <label>メールアドレス</label><br>
  <input type="email" name="email" required><br>

  <label>パスワード</label><br>
  <input type="password" name="password" required><br>

  <button type="submit">ログイン</button>
</form>

<div class="signup-link">
  <a href="register.php">新規会員登録はこちら</a>
</div>
</div>

</body>
</html>

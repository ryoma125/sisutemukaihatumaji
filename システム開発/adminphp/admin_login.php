<?php
// エラー表示（デバッグ用、本番では削除）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// セッション開始
session_start();

// データベース接続
$error_message = "";
$pdo = null;

try {
    // db-connect.phpを読み込む
    $db_path = __DIR__ . '/../require.php/db-connect.php';
    
    if (!file_exists($db_path)) {
        throw new Exception("db-connect.phpが見つかりません: " . $db_path);
    }
    
    require $db_path;
    
    // PDO接続を作成
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (Exception $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// ログインボタンが押されたときの処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input_id = $_POST["admin-id"] ?? '';
    $input_pass = $_POST["password"] ?? '';
    $input_name = $_POST["name"] ?? '';

    try {
        // データベースから管理者情報を取得
        $stmt = $pdo->prepare("SELECT * FROM Admin WHERE admin_id = ?");
        $stmt->execute([$input_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // 管理者が存在し、パスワードと名前が一致するか確認
        if ($admin && $admin['password'] === $input_pass && $admin['admin_name'] === $input_name) {
            // 認証成功
            $_SESSION["admin_login"] = true;
            $_SESSION["admin_name"] = $admin['admin_name'];
            $_SESSION["admin_id"] = $admin['admin_id'];

            // admin_product.php へ遷移
            header("Location: admin_product.php");
            exit;
        } else {
            $error_message = "ID、パスワード、または名前が正しくありません。";
        }
    } catch (PDOException $e) {
        $error_message = "ログイン処理でエラーが発生しました: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理者ログイン</title>
  <link rel="stylesheet" href="../admincss/admin_login.css">
</head>
<body>
  <header>
    <h1>Calçar</h1>
  </header>

  <main>
    <h2>管理者側ログイン画面</h2>

    <?php if (!empty($error_message)) : ?>
      <p style="color:red;"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form class="login-form" method="POST" action="">
      <label for="admin-id">管理者ID</label>
      <input type="text" id="admin-id" name="admin-id" placeholder="id" required>

      <label for="password">パスワード</label>
      <input type="password" id="password" name="password" placeholder="pass" required>

      <label for="name">名前</label>
      <input type="text" id="name" name="name" placeholder="name" required>

      <button type="submit">ログイン</button>
    </form>
  </main>
</body>
</html>
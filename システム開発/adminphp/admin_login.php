<?php
// セッション開始
session_start();


// 仮のログイン情報（本番ではデータベースで管理してください）
/*$admin_id = "admin";
$password = "1234";
$name = "Yoshii";*/

// ログインボタンが押されたときの処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input_id = $_POST["admin-id"];
    $input_pass = $_POST["password"];
    $input_name = $_POST["name"];

    if ($input_id === $admin_id && $input_pass === $password && $input_name === $name) {
        // 認証成功
        $_SESSION["admin_login"] = true;
        $_SESSION["admin_name"] = $name;

        // 🔥 ログイン成功後に admin_product.php へ遷移
        header("Location: admin_product.php");
        exit;
    } else {
        $error_message = "ID、パスワード、または名前が正しくありません。";
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

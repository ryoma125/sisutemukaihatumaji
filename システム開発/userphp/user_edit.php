<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
require '../require/db-connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $address  = $_POST['address'] ?? '';

    // パスワードを変更したいときだけ更新（空ならそのまま）
    if ($password !== '') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE User 
                SET name = ?, email = ?, password = ?, address = ?
                WHERE user_id = ?";
        $params = [$name, $email, $hashed, $address, $user_id];
    } else {
        $sql = "UPDATE User 
                SET name = ?, email = ?, address = ?
                WHERE user_id = ?";
        $params = [$name, $email, $address, $user_id];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // 更新後はマイページに戻る
    header('Location: mypage.php');
    exit;
}

// 初期表示用：現在の情報を取得（postal_codeはもう取らない）
$stmt = $pdo->prepare("SELECT name, email, address FROM User WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>会員情報変更 | Calçar</title>
  <link rel="stylesheet" href="../usercss/user_edit.css">
</head>
<body>
    <header>
    <div class="logo">Calçar</div>

    <nav class="nav">
      <div class="line"></div>
      <a href="./index.php">Home/Calçar</a>
      <div class="line"></div>
      <form class="nav-search" method="get" action="/search.php">
        <label for="nav-search-input" class="sr-only">検索ワード</label>
        <input id="nav-search-input" type="text" name="q" placeholder="search" />
        <button type="submit" class="search-btn">検索</button>
      </form>
    </nav>

    <div class="icons">
      <a href="mypage.php" class="icon">👤</a>
      <a href="cart.php" class="icon">🛒</a>
    </div>
  </header>

  <main class="form-wrap">
    <form method="post" class="user-form">
      <div class="form-group">
        <label>お名前</label>
        <input type="text" name="name"
               value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="form-group">
        <label>メールアドレス</label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="form-group">
        <label>パスワード（変更する場合のみ入力）</label>
        <input type="password" name="password" placeholder="新しいパスワード">
      </div>

      <!-- ★ 郵便番号ブロックは削除 -->

      <div class="form-group">
        <label>住所</label>
        <textarea name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <div class="submit-wrap">
        <button type="submit" class="submit-btn">登録</button>
      </div>
    </form>
  </main>
</body>
</html>

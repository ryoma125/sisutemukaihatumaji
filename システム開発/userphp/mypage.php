<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
require '../require/db-connect.php';

// ログインしてなかったらログイン画面へ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT name, address, email FROM User WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 確認画面から戻ってきたか判定
$show_confirmation = isset($_GET['confirm']) && $_GET['confirm'] === 'logout';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calçar - ユーザー情報</title>
  <link rel="stylesheet" href="../usercss/mypage.css">
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

  <main class="profile">
    <?php if ($show_confirmation): ?>
      <!-- ★ ログアウト確認画面 -->
      <div class="confirmation-box">
        <h2>ログアウトしますか？</h2>
        <p>本当にログアウトしてもよろしいですか？</p>
        
        <div class="confirmation-buttons">
          <form action="logout.php" method="post" style="display:inline;">
            <button class="confirm-btn" type="submit">ログアウト</button>
          </form>
          <a href="mypage.php">
            <button class="cancel-btn" type="button">キャンセル</button>
          </a>
        </div>
      </div>

    <?php elseif (!$user): ?>
      <p style="text-align:center; margin-top:50px;">ユーザー情報が見つかりません。</p>

    <?php else: ?>
      <div class="profile-header">
        <div class="profile-icon">👤</div>
        <div class="profile-name">
          <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <!-- ★ 確認画面へのリンク -->
        <a href="mypage.php?confirm=logout">
          <button class="logout-btn" type="button">ログアウト</button>
        </a>
      </div>

      <div class="info-box">
        <div class="info-item">
          <div class="info-icon">🏠</div>
          <div class="info-text">
            <p class="label">住所</p>
            <p><?= htmlspecialchars($user['address'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">✉️</div>
          <div class="info-text">
            <p class="label">メールアドレス</p>
            <p><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      </div>

      <div class="change-btn-wrap">
        <a href="user_edit.php">
          <button class="change-btn" type="button">変更</button>
        </a>
      </div>
    <?php endif; ?>
  </main>

<?php require '../require/footer.php'; ?>
</body>
</html>
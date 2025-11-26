<?php
session_start();
if (empty($_SESSION["admin_login"])) {
    header("Location: admin_login.php");
    exit;
}
$admin_name = isset($_SESSION["admin_name"]) ? $_SESSION["admin_name"] : "○○";
require_once "../require.php/db-connect.php";
$pdo = new PDO($connect, USER, PASS);

// ===== DB接続 =====
try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("DB接続失敗: " . $e->getMessage());
}

// ===== 削除処理 =====
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $sql = "UPDATE User SET delete_flag = 1 WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_id]);
}

// ===== 検索処理 =====
$postal = $_GET['postal'] ?? '';
$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';
$address = $_GET['address'] ?? '';

$sql = "SELECT * FROM User WHERE delete_flag = 0";
$params = [];

if ($postal !== '') {
    $sql .= " AND address LIKE ?";
    $params[] = "%{$postal}%";
}
if ($name !== '') {
    $sql .= " AND name LIKE ?";
    $params[] = "%{$name}%";
}
if ($email !== '') {
    $sql .= " AND email LIKE ?";
    $params[] = "%{$email}%";
}
if ($address !== '') {
    $sql .= " AND address LIKE ?";
    $params[] = "%{$address}%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー削除 | Calçar 管理画面</title>
    <link rel="stylesheet" href="../admincss/admin_user.css">
</head>
<body>

<!-- ========== ヘッダー ========== -->
<header class="header">
    <div class="logo">Calçar</div>
    <nav class="nav">
      <a href="admin_product.php">商品登録</a>
      <a href="admin_product_edit.php">商品管理</a>
      <a href="admin_user.php">ユーザー削除</a>
      <a href="admin_sales.php">売上管理</a>
    </nav>
    <div class="welcome">ようこそ！<?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?>さん！</div>
  </header>


<!-- ========== メイン ========== -->
<div class="container">

    <!-- 左側フォーム -->
    <div class="left-panel">
        <form method="get" class="search-form">
            <div class="form-group">
                <label>郵便番号</label>
                <div class="postal-search">
                    <input type="text" name="postal" value="<?= htmlspecialchars($postal) ?>">
                    <button type="submit">検索</button>
                </div>
            </div>

            <div class="form-group">
                <label>名前</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
            </div>

            <div class="form-group">
                <label>メールアドレス</label>
                <input type="text" name="email" value="<?= htmlspecialchars($email) ?>">
            </div>

            <div class="form-group">
                <label>住所</label>
                <textarea name="address"><?= htmlspecialchars($address) ?></textarea>
            </div>
        </form>
    </div>

    <!-- 右側リスト -->
    <div class="right-panel">
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-info">
                        <p><strong>名前</strong>　<?= htmlspecialchars($user['name']) ?></p>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                        <p>住所：<?= htmlspecialchars($user['address']) ?></p>
                    </div>
                    <form method="post" onsubmit="return confirm('本当に削除しますか？');">
                        <input type="hidden" name="delete_id" value="<?= $user['user_id'] ?>">
                        <button type="submit">削除</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>該当するユーザーが見つかりません。</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>

<?php
session_start();
if (empty($_SESSION["admin_login"])) {
    header("Location: admin_login.php");
    exit;
}
$admin_name = isset($_SESSION["admin_name"]) ? $_SESSION["admin_name"] : "○○";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>売上管理</title>
  <link rel="stylesheet" href="../admincss/admin_sales.css">
</head>
<body>
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

  <main>
    <table class="sales-table">
      <thead>
        <tr>
          <th>日付</th>
          <th>商品コード</th>
          <th>商品名</th>
          <th>単価</th>
          <th>数量</th>
          <th>合計</th>
        </tr>
      </thead>
      <tbody>
        <!-- 8行の空行 -->
        <?php for ($i = 0; $i < 8; $i++): ?>
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td class="yen">円</td>
          <td class="count">個</td>
          <td class="yen">円</td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>
  </main>
</body>
</html>

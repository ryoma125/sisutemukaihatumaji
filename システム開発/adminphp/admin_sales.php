<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION["admin_login"])) {
    header("Location: admin_login.php");
    exit;
}

$admin_name = $_SESSION["admin_name"] ?? "管理者";

require "../require/db-connect.php";

$pdo = new PDO($connect, USER, PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// ========================
// 売上データ取得
// ========================
$sql = "
  SELECT
    OrderDetail.order_detail_id,
    `Order`.order_date,
    Product.product_code,
    Product.product_name,
    Product.price,
    OrderDetail.quantity,
    (Product.price * OrderDetail.quantity) AS total
  FROM OrderDetail
  JOIN Product ON OrderDetail.product_id = Product.product_id
  JOIN `Order` ON OrderDetail.order_id = `Order`.order_id
  ORDER BY `Order`.order_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$sales_data = $stmt->fetchAll();

// 合計金額
$total_sales = 0;
foreach ($sales_data as $row) {
    $total_sales += $row['total'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
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
    <a href="admin_sales.php" class="active">売上管理</a>
  </nav>
  <div class="welcome">ようこそ <?= htmlspecialchars($admin_name) ?> さん！</div>
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
      <?php foreach ($sales_data as $sale): ?>
      <tr>
        <td><?= htmlspecialchars($sale['order_date']) ?></td>
        <td><?= htmlspecialchars($sale['product_code']) ?></td>
        <td><?= htmlspecialchars($sale['product_name']) ?></td>
        <td class="yen"><?= number_format($sale['price']) ?> 円</td>
        <td><?= htmlspecialchars($sale['quantity']) ?></td>
        <td class="yen"><?= number_format($sale['total']) ?> 円</td>
      </tr>
      <?php endforeach; ?>
    </tbody>

    <tfoot>
      <tr>
        <th colspan="5">総売上</th>
        <th class="yen"><?= number_format($total_sales) ?> 円</th>
      </tr>
    </tfoot>
  </table>
</main>

</body>
</html>

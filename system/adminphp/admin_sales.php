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
// ▼ 売上データ削除処理
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $delete_id = (int)$_POST['delete_id'];    // order_detail_id
    $order_id  = (int)$_POST['order_id'];     // 親注文ID

    // OrderDetail 削除
    $stmt = $pdo->prepare("DELETE FROM OrderDetail WHERE order_detail_id = ?");
    $stmt->execute([$delete_id]);

    // 同じ order_id の明細が残っているか確認
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM OrderDetail WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $count = $stmt->fetchColumn();

    // 明細が0なら Order も削除
    if ($count == 0) {
        $stmt = $pdo->prepare("DELETE FROM `Order` WHERE order_id = ?");
        $stmt->execute([$order_id]);
    }

    // 再読み込みして反映
    header("Location: admin_sales.php");
    exit;
}


// ========================
// ▼ 売上データ取得
// ========================
$sql = "
    SELECT 
        OrderDetail.order_detail_id,
        `Order`.order_id,
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
        <th>削除</th>
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

        <!-- ▼ 削除ボタン -->
        <td>
          <form method="POST" onsubmit="return confirm('この売上データを削除しますか？');">
            <input type="hidden" name="delete_id" value="<?= $sale['order_detail_id'] ?>">
            <input type="hidden" name="order_id" value="<?= $sale['order_id'] ?>">
            <button class="delete-btn">削除</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>

    <tfoot>
      <tr>
        <th colspan="6">総売上</th>
        <th class="yen"><?= number_format($total_sales) ?> 円</th>
      </tr>
    </tfoot>
  </table>
</main>

</body>
</html>

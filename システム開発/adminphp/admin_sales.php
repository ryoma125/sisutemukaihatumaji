<?php
// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION["admin_login"])) {
    header("Location: admin_login.php");
    exit;
}
$admin_name = isset($_SESSION["admin_name"]) ? $_SESSION["admin_name"] : "○○";

// データベース接続
$sales_data = [];
$total_sales = 0;
$error_message = "";

try {
    // db-connect.phpを読み込む（require.phpフォルダから）
    require_once __DIR__ . '/../require.php/db-connect.php';
    
    // PDO接続を作成
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // フィルター条件の取得
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    
    // SQL文の構築
    $sql = "SELECT 
                p.product_code,
                p.product_name,
                p.price,
                od.quantity,
                (p.price * od.quantity) as total
            FROM OrderDetail od
            JOIN Product p ON od.product_id = p.product_id
            ORDER BY od.order_detail_id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 合計金額の計算
    foreach ($sales_data as $sale) {
        $total_sales += $sale['total'];
    }
    
} catch (PDOException $e) {
    $error_message = "データベースエラー: " . $e->getMessage();
} catch (Exception $e) {
    $error_message = "エラー: " . $e->getMessage();
}
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
      <a href="admin_sales.php" class="active">売上管理</a>
    </nav>
    <div class="welcome">ようこそ！<?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?>さん！</div>
  </header>

  <main>
    <?php if (!empty($error_message)): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <table class="sales-table">
      <thead>
        <tr>
          <th>商品コード</th>
          <th>商品名</th>
          <th>単価</th>
          <th>数量</th>
          <th>合計</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($sales_data)): ?>
          <?php foreach ($sales_data as $sale): ?>
          <tr>
            <td><?php echo htmlspecialchars($sale['product_code'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($sale['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="yen"><?php echo number_format($sale['price']); ?>円</td>
            <td class="count"><?php echo htmlspecialchars($sale['quantity'], ENT_QUOTES, 'UTF-8'); ?>個</td>
            <td class="yen"><?php echo number_format($sale['total']); ?>円</td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="no-data-message">
              <?php echo !empty($error_message) ? 'データを取得できませんでした' : '売上データがありません'; ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($sales_data)): ?>
      <tfoot>
        <tr>
          <th colspan="4">総売上</th>
          <th class="yen"><?php echo number_format($total_sales); ?>円</th>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </main>
</body>
</html>
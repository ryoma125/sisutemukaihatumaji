<?php
session_start();
if (empty($_SESSION["admin_login"])) {
    header("Location: admin_login.php");
    exit;
}
$admin_name = isset($_SESSION["admin_name"]) ? $_SESSION["admin_name"] : "○○";

// データベース接続
require_once __DIR__ . '/../require.php/db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// 商品検索処理
$search_result = null;
if (isset($_POST['search']) && !empty($_POST['product_code_search'])) {
    $stmt = $pdo->prepare("SELECT * FROM Product WHERE product_code = ?");
    $stmt->execute([$_POST['product_code_search']]);
    $search_result = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 商品更新処理
if (isset($_POST['update']) && !empty($_POST['product_id'])) {
    $stmt = $pdo->prepare("UPDATE Product SET 
        product_name = ?,
        product_code = ?,
        brand = ?,
        price = ?,
        shipping_fee = ?,
        size = ?,
        stock = ?,
        image_url = ?
        WHERE product_id = ?");
    
    $image_url = $_POST['current_image'];
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_url = $upload_dir . time() . '_' . basename($_FILES['product_image']['name']);
        move_uploaded_file($_FILES['product_image']['tmp_name'], $image_url);
    }
    
    $stmt->execute([
        $_POST['product_name'],
        $_POST['product_code'],
        $_POST['brand'],
        $_POST['price'],
        $_POST['shipping_fee'],
        $_POST['size'],
        $_POST['stock'],
        $image_url,
        $_POST['product_id']
    ]);
    
    echo "<script>alert('商品情報を更新しました');</script>";
    $search_result = null;
}

// 商品削除処理
if (isset($_POST['delete']) && !empty($_POST['product_id'])) {
    $stmt = $pdo->prepare("DELETE FROM Product WHERE product_id = ?");
    $stmt->execute([$_POST['product_id']]);
    echo "<script>alert('商品を削除しました'); location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
}

// 商品登録処理
if (isset($_POST['register'])) {
    $image_url = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_url = $upload_dir . time() . '_' . basename($_FILES['product_image']['name']);
        move_uploaded_file($_FILES['product_image']['tmp_name'], $image_url);
    }
    
    $stmt = $pdo->prepare("INSERT INTO Product (product_name, product_code, brand, price, shipping_fee, size, stock, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['product_name'],
        $_POST['product_code'],
        $_POST['brand'],
        $_POST['price'],
        $_POST['shipping_fee'],
        $_POST['size'],
        $_POST['stock'],
        $image_url
    ]);
    
    echo "<script>alert('商品を登録しました');</script>";
}

// サイズ選択肢を生成（22.5〜30.0の0.5刻み）
function generateSizeOptions($selected = '') {
    $options = '';
    for ($size = 22.5; $size <= 30.0; $size += 0.5) {
        $size_str = number_format($size, 1);
        $selected_attr = ($selected == $size_str) ? 'selected' : '';
        $options .= "<option value='$size_str' $selected_attr>$size_str cm</option>";
    }
    return $options;
}

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'manage';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理</title>
    <link rel="stylesheet" href="../admincss/admin_product_edit.css">
</head>
<body>
    <header class="header">
        <div class="logo">Calçar</div>
        <nav class="nav">
            <a href="admin_product.php">商品登録</a>
            <a href="admin_product_edit.php" class="active">商品管理</a>
            <a href="admin_user.php">ユーザー削除</a>
            <a href="admin_sales.php">売上管理</a>
        </nav>
        <div class="welcome">ようこそ！<?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?>さん！</div>
    </header>
        
    <main>
        <div class="search-section">
            <form method="POST" class="search-form">
                <label>商品コード入力</label>
                <div class="search-input-group">
                    <input type="text" name="product_code_search" class="input-field" required>
                    <button type="submit" name="search" class="btn-search">検索</button>
                </div>
            </form>
        </div>
        
        <?php if ($search_result): ?>
        <form method="POST" enctype="multipart/form-data" class="product-form">
            <input type="hidden" name="product_id" value="<?= $search_result['product_id'] ?>">
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($search_result['image_url'] ?? '') ?>">
            
            <div class="form-layout">
                <div class="left-section">
                    <div class="image-preview">
                        <?php if (!empty($search_result['image_url']) && file_exists($search_result['image_url'])): ?>
                            <img src="<?= htmlspecialchars($search_result['image_url']) ?>" alt="商品画像">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label class="image-upload-label">
                        写真変更の場合
                        <input type="file" name="product_image" accept="image/*" class="file-input">
                    </label>
                </div>
                
                <div class="right-section">
                    <div class="stock-control">
                        <label>在庫</label>
                        <div class="stock-buttons">
                            <button type="button" class="btn-stock" onclick="changeStock(-1)">−</button>
                            <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($search_result['stock']) ?>" class="stock-input">
                            <button type="button" class="btn-stock" onclick="changeStock(1)">+</button>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>商品名</label>
                            <input type="text" name="product_name" value="<?= htmlspecialchars($search_result['product_name']) ?>" class="input-field" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>価格</label>
                            <input type="number" name="price" value="<?= htmlspecialchars($search_result['price']) ?>" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label>送料</label>
                            <input type="number" name="shipping_fee" value="<?= htmlspecialchars($search_result['shipping_fee']) ?>" class="input-field" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>商品コード</label>
                            <input type="text" name="product_code" value="<?= htmlspecialchars($search_result['product_code']) ?>" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label>ブランド</label>
                            <input type="text" name="brand" value="<?= htmlspecialchars($search_result['brand']) ?>" class="input-field" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>サイズ</label>
                            <select name="size" class="input-field" required>
                                <option value="">選択してください</option>
                                <?= generateSizeOptions($search_result['size']) ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="update" class="btn-primary">更新</button>
                        <button type="submit" name="delete" class="btn-danger" onclick="return confirm('本当に削除しますか？')">削除</button>
                    </div>
                </div>
            </div>
        </form>
        <?php elseif (isset($_POST['search'])): ?>
            <p style="color: #999; text-align: center; padding: 40px;">該当する商品が見つかりませんでした</p>
        <?php endif; ?>
    </main>
    
    <script>
        function changeStock(delta) {
            const stockInput = document.getElementById('stock');
            let currentValue = parseInt(stockInput.value) || 0;
            currentValue += delta;
            if (currentValue < 0) currentValue = 0;
            stockInput.value = currentValue;
        }
        
        // 画像プレビュー機能
        const fileInputs = document.querySelectorAll('.file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = input.closest('.left-section').querySelector('.image-preview');
                        preview.innerHTML = '<img src="' + e.target.result + '" alt="プレビュー">';
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>
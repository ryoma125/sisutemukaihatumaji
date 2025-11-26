<?php
// header.php
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calçar</title>
  <link rel="stylesheet" href="navigation.css">
</head>
<body>
  <header>
    <div class="logo">Calçar</div>

    <nav class="nav">
      <div class="line"></div>
      <a href="../userphp/index.php">Home/Calçar</a>
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
</body>
</html>

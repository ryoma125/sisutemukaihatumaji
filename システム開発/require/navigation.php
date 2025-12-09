<?php
// navigation.php
?>
<link rel="stylesheet" href="../require.css/navigation.css">
<header>
  <div class="logo">Calçar</div>

  <nav class="nav">
    <div class="line"></div>
    <a href="./index.php">Home/Calçar</a>
    <div class="line"></div>

    <form class="nav-search" method="get" action="search.php" style="position: relative;">
      <label for="nav-search-input" class="sr-only">検索ワード</label>

      <input 
        id="nav-search-input"
        type="text"
        name="q"
        placeholder="search"
        onkeyup="suggest()"
        autocomplete="off"
      />

      <div id="suggest-box" class="suggest-area"></div>

      <button type="submit" class="search-btn">検索</button>
    </form>
  </nav>

  <div class="icons">
    <a href="mypage.php" class="icon">👤</a>
    <a href="cart.php" class="icon">🛒</a> 
  </div>
</header>

<script src="../js/search_suggest.js"></script>

<style>
.suggest-area {
  position: absolute;
  top: 40px;
  left: 0;
  width: 100%;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 4px;
  display: none;
  z-index: 10;
}
.suggest-item {
  padding: 8px;
  cursor: pointer;
}
.suggest-item:hover {
  background: #eaeaea;
}
</style>

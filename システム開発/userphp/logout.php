<?php
session_start();

// セッションの中身を空にする
$_SESSION = [];

// セッションを完全に破棄
session_destroy();

// ★ ログイン画面へリダイレクト
header('Location: login.php');
exit;
?>
<?php
const SERVER ='mysql326.phy.lolipop.lan';
const DBNAME ='LAA1607573-aso2401135';
const USER   ='LAA1607573';
const PASS   ='teamYOSHII';

$connect='mysql:host='.SERVER.';dbname='.DBNAME.';charset=utf8';


// connect() 関数を定義
function connect() {
    $dsn = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';
    try {
        $pdo = new PDO($dsn, USER, PASS);
        // エラーを例外で投げる設定
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo 'DB接続失敗: ' . $e->getMessage();
        exit;
    }
}
?>

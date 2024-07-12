<?php
// データベースファイルのパスです
$dbPath = 'path_to_your_database/your_database_name.sqlite';

try {
    // SQLiteデータベースファイルを作成またはオープン
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Usersテーブルを作成
    $pdo->exec("CREATE TABLE IF NOT EXISTS Users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        last_name TEXT NOT NULL,
        first_name TEXT NOT NULL,
        birthday DATE NOT NULL,
        address TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL
    )");

    echo "データベースとテーブルの作成が完了しました。";

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
}
?>
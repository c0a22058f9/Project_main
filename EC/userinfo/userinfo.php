<?php
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
$servername = "localhost";
$username = "user1";
$password = "passwordA1!";
$dbname = "ecdatabase";
define('DSN', 'mysql:host=' . $servername . ';dbname=' . $dbname);
define('DB_USER', $username);
define('DB_PASS', $password);

// セッションIDのチェック
$session_id = session_id();
if (empty($session_id)) {
    die('セッションが存在しないか、無効です。');
}

// データベース接続
try {
    $conn = new PDO(DSN, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("接続失敗: " . $e->getMessage());
}

// セッションIDからユーザーIDを取得
$getUserIdSql = "SELECT user_id FROM sessions WHERE session_id = ?";
try {
    $stmt = $conn->prepare($getUserIdSql);
    $stmt->execute([$session_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die('有効なユーザーが見つかりません。');
    }
    $user_id = $user['user_id'];
} catch (PDOException $e) {
    die("ユーザーID取得エラー: " . $e->getMessage());
}

// SQLインジェクションを可能にするため、直接SQL文を組み立てる
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $user_id; // SQLインジェクションのために変更
$getUserInfoSql = "SELECT user_id, last_name, first_name, birthday, address, email FROM users WHERE user_id = $user_id"; // 脆弱なクエリ
try {
    $stmt = $conn->query($getUserInfoSql); // prepareではなくqueryを使用
    $userInfoList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$userInfoList) {
        die('ユーザー情報が見つかりません。');
    }
} catch (PDOException $e) {
    die("ユーザー情報取得エラー: " . $e->getMessage());
}

// ユーザーのタグを取得
$getTagsSql = "SELECT tag_id, tag FROM tags WHERE user_id = ?";
try {
    $stmt = $conn->prepare($getTagsSql);
    $stmt->execute([$user_id]);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("タグ取得エラー: " . $e->getMessage());
}

// タグの削除
if (isset($_POST['delete_tag'])) {
    $tag_id = $_POST['tag_id'];
    $deleteTagSql = "DELETE FROM tags WHERE tag_id = ?";
    try {
        $stmt = $conn->prepare($deleteTagSql);
        $stmt->execute([$tag_id]);
        // Refresh tags list after deletion
        $stmt = $conn->prepare($getTagsSql);
        $stmt->execute([$user_id]);
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("タグ削除エラー: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ユーザー情報</title>
    <!-- Materialize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .collection-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        body {
            font-family: "SimSun";
            background-image: url("/Project_main/EC/image/background.jpg");
            background-color:rgba(255,255,255,0.8);
            background-blend-mode:lighten;
        }
        .carousel .carousel-item {
            height: 300px;
        }
        .carousel-item img {
            height: 100%;
            object-fit: cover;
        }
        .nav-wrapper {
            background-image: url(/Project_main/EC/image/nav-background.jpg);
        }
        .nav-content {
            background-color: #919191;
            max-width: 1280px;
            margin: 0 auto;
            width: 70%;
            text-align: center;
        }
        .padding {
            padding-top: 30px;
        }
        .nav-font {
            font-family: "SimSun";
            margin-left: 100px;
            font-weight:bold;
        }
        .card .card-image img {
            height: 200px;
            object-fit: cover;
        }

        footer {
            background-color: #828e9c !important;
        }

        footer h5, footer p {
            color: black;
        }
        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }

    </style>
</head>
<body>
    <!-- Navbar -->
    <header>
        <nav>
            <div class="nav-wrapper">
                <a href="/Project_main/EC/main.php" class="brand-logo black-text nav-font">Le Lien Luxe</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <?php if (!empty($user_id)) : ?>
                        <li><a href="/Project_main/EC/cart/cartview.php" class="waves-effect waves-light btn">商品カート</a></li>
                        <li><a href="/Project_main/EC/userinfo/userinfo.php" class="waves-effect waves-light btn">ユーザページ</a></li>
                    <?php else : ?>
                        <li><a href="" class="waves-effect waves-light btn disabled">商品カート</a></li>
                        <li><a href="" class="waves-effect waves-light btn disabled">ユーザページ</a></li>
                    <?php endif; ?>
                    <li><a href="/Project_main/EC/login/login.html" class="waves-effect waves-light btn">ログイン</a></li>
                    <li><a href="/Project_main/EC/login/register.html" class="waves-effect waves-light btn">新規登録</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">ユーザー情報</span>
                        <?php foreach ($userInfoList as $userInfo): ?>
                            <p><strong>氏名:</strong> <?php echo htmlspecialchars($userInfo['last_name'] . ' ' . $userInfo['first_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>生年月日:</strong> <?php echo htmlspecialchars($userInfo['birthday'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>住所:</strong> <?php echo htmlspecialchars($userInfo['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>メールアドレス:</strong> <?php echo htmlspecialchars($userInfo['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endforeach; ?>
                        <button class="btn waves-effect waves-light" onclick="location.href='update_user_info.php'">編集</button>
                        <button class="btn waves-effect waves-light" onclick="location.href='/Project_main/EC/main.php'">メインページへ</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- タグ表示と削除フォーム -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">保存されているタグ</span>
                        <ul class="collection">
                            <?php foreach ($tags as $tag): ?>
                                <li class="collection-item">
                                    <span><?php echo htmlspecialchars($tag['tag'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <form method="post" action="">
                                        <input type="hidden" name="tag_id" value="<?php echo $tag['tag_id']; ?>">
                                        <button type="submit" name="delete_tag" class="btn-floating btn-small waves-effect waves-light red"><i class="material-icons">delete</i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="page-footer">
        <div class="container">
            <div class="row">
                <div class="col l6 s12">
                    <h5 class="black-text">Le Lien Luxe</h5>
                    <p class="black-text text-lighten-4">洗練されたあなたのための特別な一品を</p>
                </div>
                <div class="col l4 offset-l2 s12">
                    <ul>
                        <li><a class="black-text text-lighten-3" href="/Project_main/EC/cart/cartview.php">商品カート</a></li>
                        <li><a class="black-text text-lighten-3" href="/Project_main/EC/login/login.html">ログイン</a></li>
                        <li><a class="black-text text-lighten-3" href="/Project_main/EC/login/register.html">新規登録</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                &copy; 2024 Shopping Site. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>

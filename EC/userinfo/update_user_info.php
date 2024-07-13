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

// ユーザー情報を取得
$getUserInfoSql = "SELECT last_name, first_name, birthday, address, email FROM users WHERE user_id = ?";
try {
    $stmt = $conn->prepare($getUserInfoSql);
    $stmt->execute([$user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userInfo) {
        die('ユーザー情報が見つかりません。');
    }
} catch (PDOException $e) {
    die("ユーザー情報取得エラー: " . $e->getMessage());
}

// フォームの処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $zipcode = $_POST['zipcode'] ?? '';
    $prefecture = $_POST['prefecture'] ?? '';
    $city = $_POST['city'] ?? '';
    $street = $_POST['street'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 入力データの検証
    if (empty($email) || empty($street) || empty($password)) {
        die('必須フィールドが入力されていません。');
    }

    // パスワードのハッシュ化
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 住所を結合
    $address = $zipcode . ' ' . $prefecture . ' ' . $city . ' ' . $street;

    // ユーザー情報の更新
    $updateUserInfoSql = "UPDATE users SET address = ?, email = ?, password_hash = ? WHERE user_id = ?";
    try {
        $stmt = $conn->prepare($updateUserInfoSql);
        $stmt->execute([$address, $email, $passwordHash, $user_id]);
        $updateMessage = "ユーザー情報が更新されました。";
    } catch (PDOException $e) {
        die("ユーザー情報更新エラー: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ユーザー情報更新</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <script>
        function populateAddress() {
            AjaxZip3.zip2addr('zipcode', '', 'prefecture', 'city');
            setTimeout(function() {
                M.updateTextFields();
            }, 100);
        }
    </script>
</head>
<body>
    <nav>
        <div class="nav-wrapper">
            <a href="#" class="brand-logo center">ユーザー情報更新</a>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <h4>ユーザー情報更新</h4>
                <?php if (!empty($updateMessage)): ?>
                    <p class="green-text"><?php echo htmlspecialchars($updateMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="input-field">
                        <input type="text" name="zipcode" id="zipcode" value="<?php echo htmlspecialchars(explode(' ', $userInfo['address'])[0], ENT_QUOTES, 'UTF-8'); ?>" onkeyup="populateAddress();">
                        <label for="zipcode" class="active">郵便番号</label>
                    </div>
                    <div class="input-field">
                        <input type="text" name="prefecture" id="prefecture" value="<?php echo htmlspecialchars(explode(' ', $userInfo['address'])[1], ENT_QUOTES, 'UTF-8'); ?>">
                        <label for="prefecture" class="active">都道府県</label>
                    </div>
                    <div class="input-field">
                        <input type="text" name="city" id="city" value="<?php echo htmlspecialchars(explode(' ', $userInfo['address'])[2], ENT_QUOTES, 'UTF-8'); ?>">
                        <label for="city" class="active">市区町村</label>
                    </div>
                    <div class="input-field">
                        <input type="text" name="street" id="street" value="<?php echo htmlspecialchars(explode(' ', $userInfo['address'])[3], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <label for="street" class="active">住所</label>
                    </div>
                    <div class="input-field">
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($userInfo['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <label for="email" class="active">メールアドレス</label>
                    </div>
                    <div class="input-field">
                        <input type="password" name="password" id="password" required>
                        <label for="password">新しいパスワード</label>
                    </div>
                    <button class="btn waves-effect waves-light" type="submit">更新</button>
                </form>
                <button onclick="location.href='../index.php'">メインページへ</button>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// エラーを表示
ini_set('display_errors', "On");
error_reporting(E_ALL);
session_start();
require 'database_config.php'; // データベース接続情報を含むファイル

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$fingerprint = $_POST['fingerprint'] ?? '';
$hcaptcha_response = $_POST['h-captcha-response'] ?? '';

if (empty($email) || empty($password) || empty($hcaptcha_response)) {
    echo "<script>alert('ログイン情報とhCAPTCHAを入力してください。'); location.href='login.html';</script>";
    exit;
}

try {
    $pdo = new PDO(DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verify hCAPTCHA
    $secret_key = 'ES_89c6c49805844ec4a6218042defdf3b5'; // Replace with your secret key
    $verify_url = 'https://hcaptcha.com/siteverify';
    $data = array(
        'secret' => $secret_key,
        'response' => $hcaptcha_response
    );

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($verify_url, false, $context);
    $result_json = json_decode($result);

    if (!$result_json->success) {
        die('hCAPTCHA verification failed. Please try again.');
    }

    // Proceed with database query if hCAPTCHA verification succeeds
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // ログイン成功
        $_SESSION['user_id'] = $user['user_id'];
        $session_id = session_id();

        // Check for existing session
        $check_sql = "SELECT * FROM sessions WHERE session_id = :session_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // Existing session found
            $existing_session = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing_session['user_id'] != $_SESSION['user_id']) {
                // Different user, replace session
                $delete_sql = "DELETE FROM sessions WHERE session_id = :session_id";
                $delete_stmt = $pdo->prepare($delete_sql);
                $delete_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
                $delete_stmt->execute();

                $insert_sql = "INSERT INTO sessions (session_id, user_id) VALUES (:session_id, :user_id)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $insert_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
                $insert_stmt->execute();
            }
        } else {
            // New session, insert
            $insert_sql = "INSERT INTO sessions (session_id, user_id) VALUES (:session_id, :user_id)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $insert_stmt->bindParam(':session_id', $session_id, PDO::PARAM_STR);
            $insert_stmt->execute();
        }

        // Here you can save the fingerprint to the database or log it
        $fp_sql = "INSERT INTO fingerprints (user_id, fingerprint) VALUES (:user_id, :fingerprint)";
        $fp_stmt = $pdo->prepare($fp_sql);
        $fp_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $fp_stmt->bindParam(':fingerprint', $fingerprint, PDO::PARAM_STR);
        $fp_stmt->execute();

        header('Location: ../index.html');
        exit;
    } else {
        die('ログイン情報が正しくありません。');
    }
} catch (PDOException $e) {
    die('エラー: ' . $e->getMessage());
}
?>

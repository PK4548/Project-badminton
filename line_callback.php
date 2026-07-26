<?php
session_start();
require_once 'includes/config.php';

$client_id = LINE_LOGIN_CHANNEL_ID;
$client_secret = LINE_LOGIN_CHANNEL_SECRET;
$redirect_uri = LINE_LOGIN_CALLBACK_URL;

if (isset($_GET['code']) && $_GET['state'] === $_SESSION['line_state']) {
    $code = $_GET['code'];

    // 1. นำ code ไปแลก Access Token
    $url = 'https://api.line.me/oauth2/v2.1/token';
    $data = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'client_id' => $client_id,
        'client_secret' => $client_secret
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['access_token'])) {
        $access_token = $response['access_token'];

        // 2. นำ Access Token ไปดึง Profile (เพื่อเอา User ID)
        $ch = curl_init('https://api.line.me/v2/profile');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $profile = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($profile['userId'])) {
            $line_user_id = $profile['userId'];
            $user_id = $_SESSION['user_id'];

            // 3. บันทึกลงฐานข้อมูล
            $stmt = $pdo->prepare("UPDATE users SET line_user_id = ? WHERE user_id = ?");
            if ($stmt->execute([$line_user_id, $user_id])) {
                ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบสำเร็จ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-success-card { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; }
    </style>
</head>
<body>
    <div class="login-success-card">
        <div class="mb-4">
            <h4 class="text-success">เข้าสู่ระบบสำเร็จ!</h4>
            <p class="text-muted">เพื่อให้ไม่พลาดการแจ้งเตือนการจองสนาม<br>กรุณาเพิ่มเพื่อนกับเราก่อนเข้าใช้งาน</p>
        </div>
        
        <div class="mb-4">
            <a href="https://lin.ee/7zVF1qD" target="_blank">
                <img src="https://scdn.line-apps.com/n/line_add_friends/btn/th.png" alt="เพิ่มเพื่อน" height="45" border="0">
            </a>
        </div>

        <hr>
        
        <div>
            <a href="dashboard.php" class="btn btn-primary w-100">เข้าสู่หน้าหลักของระบบ</a>
        </div>
    </div>
</body>
</html>
<?php
exit();
?><?php
            } else {
                echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
            }
        }
    }
} else {
    echo "การเชื่อมต่อถูกยกเลิก หรือค่า State ไม่ถูกต้อง";
}
?>
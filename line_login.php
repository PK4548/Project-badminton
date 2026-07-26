<?php
session_start();
require_once 'includes/config.php';
$client_id = LINE_LOGIN_CHANNEL_ID;
$redirect_uri = LINE_LOGIN_CALLBACK_URL;
$state = bin2hex(random_bytes(16)); // สร้างรหัสสุ่มเพื่อความปลอดภัย
$_SESSION['line_state'] = $state;

$url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&state={$state}&scope=profile%20openid&bot_prompt=aggressive";
header("Location: {$url}");
exit();
?>
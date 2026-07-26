<?php
// ฟังก์ชันส่งข้อความผ่าน LINE Messaging API
require_once 'config.php';
function sendLinePushMessage($to, $message) {
    $channel_access_token = LINE_MESSAGING_CHANNEL_TOKEN;
    
    $url = 'https://api.line.me/v2/bot/message/push';
    
    $data = [
        'to' => $to,
        'messages' => [
            [
                'type' => 'text',
                'text' => $message
            ]
        ]
    ];
    
    $post = json_encode($data);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $channel_access_token
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
?>
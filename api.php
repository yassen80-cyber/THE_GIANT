<?php
// 1. إعدادات الأمان ومنع الوصول الخارجي
$allowed_origin = "https://thegiant10.com"; // رابط موقعك فقط
header("Access-Control-Allow-Origin: $allowed_origin");
header('Content-Type: application/json');

// منع الدخول المباشر من المتصفح (رقم 3 اللي سألت عليها)
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, $allowed_origin) === false) {
    http_response_code(403);
    die(json_encode(["error" => "Access Denied: Unauthorized Referrer"]));
}

// 2. بيانات المفتاح
$API_KEY = "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";
$API_URL = "https://api.groq.com/openai/v1/chat/completions";

// 3. استقبال البيانات
$input = file_get_contents('php://input');
if (!$input) {
    die(json_encode(["error" => "No input provided"]));
}

// 4. إرسال الطلب عبر cURL
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input); // نرسل الـ JSON الخام كما جاء من المتصفح
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $API_KEY,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($input)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
} else {
    http_response_code($httpCode);
    echo $response;
}

curl_close($ch);
?>

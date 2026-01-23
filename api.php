<?php
// إعدادات الأمان
header('Content-Type: application/json');

// ضع مفتاح الـ API الخاص بك هنا - سيبقى مخفياً داخل السيرفر
$API_KEY = "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";
$API_URL = "https://api.groq.com/openai/v1/chat/completions";

// استقبال البيانات المرسلة من الـ JavaScript
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// تجهيز الطلب المرسل لـ Groq
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $API_KEY,
    'Content-Type: application/json'
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

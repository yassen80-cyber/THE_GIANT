<?php
/**
 * THE GIANT - API Proxy
 * هذا الملف يعمل كجدار حماية لإخفاء مفتاح الـ API ومنع الاستخدام غير المصرح به.
 */

// 1. إعدادات الأمان - السماح فقط لموقعك بالوصول (CORS)
$my_domain = "https://thegiant10.com"; // استبدله برابط موقعك الحقيقي
header("Access-Control-Allow-Origin: $my_domain");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// 2. منع الدخول المباشر للملف من المتصفح (رقم 3 المطلبوبة)
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (empty($referer) || strpos($referer, $my_domain) === false) {
    http_response_code(403);
    echo json_encode(["error" => "Access Denied: Unauthorized request origin."]);
    exit;
}

// 3. بيانات المفتاح والسيرفر (مخفية تماماً عن الزوار)
$API_KEY = "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";
$API_URL = "https://api.groq.com/openai/v1/chat/completions";

// 4. استقبال البيانات من الجافا سكريبت
$json_input = file_get_contents('php://input');
if (empty($json_input)) {
    http_response_code(400);
    echo json_encode(["error" => "Bad Request: No data received."]);
    exit;
}

// 5. تجهيز وإرسال الطلب لشركة Groq باستخدام cURL
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_input); // نرسل البيانات كما جاءت
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $API_KEY,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_input)
]);

// ضبط وقت الانتظار (Timeout)
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 6. معالجة الرد وإرساله للمتصفح
if ($curl_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection error: ' . $curl_error]);
} else {
    http_response_code($httpCode);
    echo $response;
}

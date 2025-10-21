<?php
// /api/test-env.php




echo "SMTP Host: " . getenv('SMTP_HOST') . "<br>";
echo "SMTP Username: " . getenv('SMTP_USERNAME') . "<br>";

// 設定響應頭為 JSON 格式，方便查看
header('Content-Type: application/json');

// 將 $_SERVER 超全局變數中的所有內容，以 JSON 格式打印出來
echo json_encode($_SERVER, JSON_PRETTY_PRINT);



?>



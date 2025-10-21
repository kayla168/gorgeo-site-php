<?php
// /api/fix-kit.php

// 關鍵修改：因為 data.php 現在和它在同一個 api/ 目錄下，路徑變得更簡單了
require_once __DIR__ . '/data.php';

// 從URL獲取用戶想要哪個資料
$kit_slug = isset($_GET['kit']) ? $_GET['kit'] : '';

// 檢查資料是否存在
if (!isset($kits[$kit_slug])) {
    header("HTTP/1.0 404 Not Found");
    readfile($_SERVER['DOCUMENT_ROOT'] . '/drop/error.html');
    exit;
}

// 獲取當前資料的所有內容
$current_kit = $kits[$kit_slug];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($current_kit['page_title']); ?> - Gorgeo Fasteners</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div id="header-placeholder"></div>

    <main class="section" style="padding: 80px 0;">
        <div class="container" style="max-width: 600px; margin: 0 auto; text-align: center;">

            <h1><?php echo htmlspecialchars($current_kit['page_title']); ?></h1>

            <ul style="text-align: left; display: inline-block; margin-top: 20px; margin-bottom: 40px; list-style-position: inside; padding-left: 0;">
                <?php foreach ($current_kit['description'] as $line): ?>
                    <li style="margin-bottom: 10px;"><?php echo htmlspecialchars($line); ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="card" style="padding: 30px; border: 1px solid #e9ecef; border-radius: 8px;">
                <h3 style="margin-top: 0;"><?php echo htmlspecialchars($current_kit['form_title']); ?></h3>
                
                <form action="/api/handle-download-blog.php" method="POST">
                    <input type="hidden" name="document_type" value="<?php echo htmlspecialchars($current_kit['file_key']); ?>">
                    
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Work Email" required>
                    </div>
                    
                    <button type="submit" class="cta-button w-100"><?php echo htmlspecialchars($current_kit['button_text']); ?></button>
                </form>
            </div>

        </div>
    </main>

    <div id="footer-placeholder"></div>
    
    <script id="main-include-script" src="/assets/js/includes.js"></script>
    <script src="/assets/js/form-loading.js"></script>
</body>
</html>
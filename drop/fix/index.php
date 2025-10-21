<?php
// /drop/fix/index.php

// 开启错误显示，用于调试
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. 引入我们的内容数据 (只引入一次)
require_once 'data.php';

// 2. 从URL获取用户想要哪个资料
// (这需要服务器配置URL重写，将 /drop/coating-fit-jam/ 指向 /drop/fix/index.php?kit=coating-fit-jam)
$kit_slug = isset($_GET['kit']) ? $_GET['kit'] : '';

// 3. 检查资料是否存在，如果不存在则显示错误
if (!isset($kits[$kit_slug])) {
    header("HTTP/1.0 404 Not Found");
    echo "Sorry, the requested resource was not found.";
    exit;
}

// 4. 获取当前资料的所有内容
$current_kit = $kits[$kit_slug];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 动态设置页面标题 -->
    <title><?php echo htmlspecialchars($current_kit['page_title']); ?> - Gorgeo Fasteners</title>
    <!-- 确保这里的路径是正确的，相对于网站根目录 -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <!-- 确保你的JS可以正确加载header和footer -->
    <div id="header-placeholder"></div>

    <main class="section" style="padding: 80px 0;">
        <div class="container" style="max-width: 600px; margin: 0 auto; text-align: center;">

            <!-- 动态填充标题 -->
            <h1><?php echo htmlspecialchars($current_kit['page_title']); ?></h1>

            <!-- 动态填充价值描述 -->
            <ul style="text-align: left; display: inline-block; margin-top: 20px; margin-bottom: 40px; list-style-position: inside; padding-left: 0;">
                <?php foreach ($current_kit['description'] as $line): ?>
                    <li style="margin-bottom: 10px;"><?php echo htmlspecialchars($line); ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="card" style="padding: 30px; border: 1px solid #e9ecef; border-radius: 8px;">
                <!-- 动态填充表单标题 -->
                <h3 style="margin-top: 0;"><?php echo htmlspecialchars($current_kit['form_title']); ?></h3>
                
                <!-- 确保action路径正确，相对于网站根目录 -->
                <form action="../../api/handle-download-blog.php" method="POST">
                    <!-- 关键一步：隐藏字段，告诉后端要发送哪个资料 -->
                    <input type="hidden" name="document_type" value="<?php echo htmlspecialchars($current_kit['file_key']); ?>">
                    
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Work Email" required>
                    </div>
                    
                    <!-- 动态填充按钮文字 -->
                    <button type="submit" class="cta-button w-100"><?php echo htmlspecialchars($current_kit['button_text']); ?></button>
                </form>
            </div>

        </div>
    </main>

    <div id="footer-placeholder"></div>
    <!-- 确保这里的路径是正确的，相对于网站根目录 -->
    
      <script id="main-include-script" src="../../assets/js/includes.js"></script>
 <script src="../../assets/js/form-loading.js"></script>
</body>
</html>

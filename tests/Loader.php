<?php

// 1. 设置自动加载（加载src目录下的类）
spL_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../src/';  // 从tests目录向上找到src

    // 转换命名空间为文件路径
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. 引入上级vendor目录的Composer自动加载
require_once __DIR__ . '/../vendor/autoload.php';

// 3. 测试日志函数
function test_log($message): void
{
    echo "[TEST] " . $message . PHP_EOL;
}
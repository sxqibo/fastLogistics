<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 读取之前保存的处理号
$processCode = @file_get_contents(__DIR__ . '/last_process_code.txt');
if (!$processCode) {
    die("请先创建包裹并获取处理号！\n");
}

// 获取包裹标签
$result = $app->getParcelLabel($processCode);
print_r($result);


// 成功返回示例：
// Array
// (
//     [Code] => 0
//     [Message] => success
//     [Data] => Array
//         (
//              [content] =>  %PDF-1.4
//              [httpCode] => 200
//              [contentType] => application/pdf
//         )
// )
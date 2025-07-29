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

// 下载投递照片
$result = $app->downloadDeliveryPhotos($processCode);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [ProcessCode] => SBXPAA0000038638YQ
 *             [Photos] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [Url] => http://api-sbx.wanbexpress.com/photos/SBXPAA0000038638YQ_1.jpg
 *                             [Format] => JPG
 *                             [CreatedTime] => 2024-01-29 16:15:00
 *                         )
 *                     [1] => Array
 *                         (
 *                             [Url] => http://api-sbx.wanbexpress.com/photos/SBXPAA0000038638YQ_2.jpg
 *                             [Format] => JPG
 *                             [CreatedTime] => 2024-01-29 16:15:00
 *                         )
 *                 )
 *         )
 * )
 */ 
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

// 上传尾程派送单号与面单参数
$params = [
    'TrackingNumber' => 'USPS1234567890',  // 尾程派送单号
    'LabelUrl' => 'http://example.com/label.pdf',  // 尾程面单URL
    'LabelFormat' => 'PDF',                // 面单格式：PDF/PNG
    'Remark' => '尾程派送信息'             // 备注
];

// 上传尾程派送单号与面单
$result = $app->uploadLastMileInfo($processCode, $params);
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
 *             [TrackingNumber] => USPS1234567890
 *             [UpdateTime] => 2024-01-29 16:00:00
 *         )
 * )
 */ 
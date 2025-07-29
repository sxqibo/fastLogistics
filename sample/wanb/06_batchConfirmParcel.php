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

// 批量确认交运包裹参数
$params = [
    'ProcessCodes' => [
        $processCode
        // 可以添加更多处理号
    ]
];

// 批量确认交运包裹
$result = $app->batchConfirmParcel($params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [SuccessCount] => 1
 *             [FailCount] => 0
 *             [Details] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [ProcessCode] => SBXPAA0000038638YQ
 *                             [Success] => true
 *                             [Message] => success
 *                             [ReferenceId] => REF1000000006
 *                             [TrackingNumber] => JD0002255030609022
 *                         )
 *                 )
 *         )
 * )
 */ 
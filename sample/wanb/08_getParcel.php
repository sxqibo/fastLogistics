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

// 获取包裹
$result = $app->getParcel($processCode);
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
 *             [CustomerOrderNumber] => TEST1234567890
 *             [TrackingNumber] => JD0002255030609022
 *             [Status] => Pending
 *             [ShippingMethod] => USPS
 *             [Weight] => 2.5
 *             [Pieces] => 1
 *             [Warehouse] => SZ
 *             [InsuranceType] => 0
 *             [InsuranceAmount] => 0
 *             [SourceCode] => API
 *             [CreatedTime] => 2024-01-29 15:30:00
 *             [Sender] => Array(...)
 *             [Recipient] => Array(...)
 *             [Items] => Array(...)
 *         )
 * )
 */ 
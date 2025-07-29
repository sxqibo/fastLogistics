<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 读取之前保存的袋号
$bagCode = @file_get_contents(__DIR__ . '/last_bag_code.txt');
if (!$bagCode) {
    die("请先创建来货/揽收袋并获取袋号！\n");
}

// 获取来货/揽收袋信息
$result = $app->getBag($bagCode);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [BagCode] => SBXBAA0000038638YQ
 *             [CustomerBagNumber] => BAG1234567890
 *             [Warehouse] => SZ
 *             [Weight] => 12.5
 *             [Length] => 55
 *             [Width] => 45
 *             [Height] => 35
 *             [ExpectedArrivalTime] => 2024-02-02
 *             [Status] => Pending
 *             [CreatedTime] => 2024-01-29 16:20:00
 *             [UpdateTime] => 2024-01-29 16:25:00
 *             [Parcels] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [ProcessCode] => SBXPAA0000038639YQ
 *                             [CustomerOrderNumber] => TEST1234567891
 *                             [ShippingMethod] => USPS
 *                             [Weight] => 3.5
 *                             [Status] => Pending
 *                             [CreatedTime] => 2024-01-29 16:30:00
 *                         )
 *                 )
 *         )
 * )
 */ 
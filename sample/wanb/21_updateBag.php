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

// 修改来货/揽收袋信息参数
$params = [
    'Weight' => 12.5,                       // 新的重量(KG)
    'Length' => 55,                         // 新的长(CM)
    'Width' => 45,                          // 新的宽(CM)
    'Height' => 35,                         // 新的高(CM)
    'ExpectedArrivalTime' => '2024-02-02',  // 新的预计到仓时间
    'Remark' => '修改重量和尺寸'            // 修改备注
];

// 修改来货/揽收袋信息
$result = $app->updateBag($bagCode, $params);
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
 *             [Weight] => 12.5
 *             [Length] => 55
 *             [Width] => 45
 *             [Height] => 35
 *             [ExpectedArrivalTime] => 2024-02-02
 *             [UpdateTime] => 2024-01-29 16:25:00
 *         )
 * )
 */ 
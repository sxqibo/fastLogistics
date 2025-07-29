<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 搜索包裹参数
$params = [
    'CustomerOrderNumber' => 'TEST',     // 客户订单号（模糊匹配）
    'ProcessCode' => '',                 // 处理号（精确匹配）
    'TrackingNumber' => '',              // 跟踪单号（精确匹配）
    'Status' => 'Pending',               // 状态（精确匹配）
    'ShippingMethod' => 'USPS',          // 运输方式（精确匹配）
    'Warehouse' => 'SZ',                 // 仓库代码（精确匹配）
    'CreateTimeStart' => '2024-01-01',   // 创建时间开始
    'CreateTimeEnd' => '2024-12-31',     // 创建时间结束
    'PageSize' => 10,                    // 每页记录数
    'PageIndex' => 1                     // 页码
];

// 搜索包裹
$result = $app->searchParcels($params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [Total] => 1
 *             [PageSize] => 10
 *             [PageIndex] => 1
 *             [Items] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [ProcessCode] => SBXPAA0000038638YQ
 *                             [CustomerOrderNumber] => TEST1234567890
 *                             [TrackingNumber] => JD0002255030609022
 *                             [Status] => Pending
 *                             [ShippingMethod] => USPS
 *                             [Weight] => 2.5
 *                             [Pieces] => 1
 *                             [Warehouse] => SZ
 *                             [CreatedTime] => 2024-01-29 15:30:00
 *                         )
 *                 )
 *         )
 * )
 */ 
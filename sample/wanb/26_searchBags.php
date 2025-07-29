<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 搜索来货/揽收袋参数
$params = [
    'CustomerBagNumber' => 'BAG',           // 客户袋号（支持模糊搜索）
    'Status' => 'Pending',                  // 状态（Pending:待确认, Confirmed:已确认, Deleted:已删除）
    'Warehouse' => 'SZ',                    // 仓库代码
    'CreateTimeStart' => '2024-01-01',      // 创建时间开始
    'CreateTimeEnd' => '2024-01-31',        // 创建时间结束
    'PageSize' => 10,                       // 每页记录数
    'PageIndex' => 1                        // 当前页码
];

// 搜索来货/揽收袋
$result = $app->searchBags($params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [Total] => 2
 *             [PageSize] => 10
 *             [PageIndex] => 1
 *             [Items] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [BagCode] => SBXBAA0000038638YQ
 *                             [CustomerBagNumber] => BAG1234567890
 *                             [Warehouse] => SZ
 *                             [Weight] => 12.5
 *                             [Status] => Pending
 *                             [CreatedTime] => 2024-01-29 16:20:00
 *                             [UpdateTime] => 2024-01-29 16:25:00
 *                         )
 *                     [1] => Array
 *                         (
 *                             [BagCode] => SBXBAA0000038637YQ
 *                             [CustomerBagNumber] => BAG1234567889
 *                             [Warehouse] => SZ
 *                             [Weight] => 8.5
 *                             [Status] => Pending
 *                             [CreatedTime] => 2024-01-29 15:20:00
 *                             [UpdateTime] => 2024-01-29 15:25:00
 *                         )
 *                 )
 *         )
 * )
 *
 * 注意：
 * 1. 所有搜索条件都是可选的
 * 2. CustomerBagNumber 支持模糊搜索
 * 3. 时间格式为：YYYY-MM-DD 或 YYYY-MM-DD HH:mm:ss
 */ 
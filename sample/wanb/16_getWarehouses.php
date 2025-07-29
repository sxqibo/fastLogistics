<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 获取仓库
$result = $app->getWarehouses();
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [Code] => SZ
 *                     [Name] => 深圳仓
 *                     [NameEn] => Shenzhen Warehouse
 *                     [Address] => 深圳市宝安区XX路XX号
 *                     [Status] => 1
 *                     [Remark] => 主要仓库
 *                 )
 *         )
 * )
 */ 
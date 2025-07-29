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

// 修改包裹预报重量参数
$params = [
    'Weight' => 3.0,    // 新的重量(KG)
    'Remark' => '修改重量'  // 修改备注
];

// 修改包裹预报重量
$result = $app->updateParcelWeight($processCode, $params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [ProcessCode] => WB12345678
 *             [Weight] => 3.0
 *             [UpdateTime] => 2024-01-29 15:35:00
 *         )
 * )
 */ 
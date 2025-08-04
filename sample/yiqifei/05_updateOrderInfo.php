<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 更新订单信息参数
$params = [
    'insideNumber' => 'FEISZ1708169603YQ',   // 订单号（一起飞系统的订单号）
    'weight'       => 2.0,             // 新重量(KG)
    'boxLength'    => 35,              // 新长度(CM)
    'boxWidth'     => 25,              // 新宽度(CM)
    'boxHeight'    => 15,              // 新高度(CM)
];

// 更新订单信息
$result = $app->updateOrderInfo($params);


print_r($result);

echo "=== 更新订单信息结果 ===\n";
if ($result['flag']) {
    echo "更新成功！\n";
    if (isset($result['obj'])) {
        echo "更新后的信息：\n";
        print_r($result['obj']);
    }
} else {
    echo "更新失败！\n";
    echo "错误信息: " . ($result['msg'] ?? $result['obj'] ?? '未知错误') . "\n";
}


/**
 * Array
 * (
 * [code] =>
 * [flag] => 1
 * [msg] =>
 * [obj] =>
 * [rows] => Array
 * (
 * )
 *
 * [total] => 0
 * )
 * === 更新订单信息结果 ===
 * 更新成功！
 */
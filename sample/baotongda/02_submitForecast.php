<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 获取上一步创建的订单号
$reference_no = @file_get_contents(__DIR__ . '/last_reference_no.txt');
if (!$reference_no) {
    die("请先运行创建订单示例！\n");
}

// 提交预报参数
$params = [
    'reference_no' => $reference_no,  // 客户参考号
];

// 提交预报
$result = $app->submitForecast($params);

print_r($result);

/**
 * Array
 * (
 * [data] => Array
 * (
 * [order_id] => 2948637
 * [refrence_no] => TEST1753774835
 * [shipping_method_no] =>
 * [channel_hawbcode] =>
 * [consignee_areacode] =>
 * [station_code] =>
 * )
 *
 * [success] => 1
 * [cnmessage] => 订单提交预报成功
 * [enmessage] => 订单提交预报成功
 * [order_id] => 2948637
 * )
 */
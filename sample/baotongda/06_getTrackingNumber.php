<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 读取之前保存的订单号
$referenceNo = @file_get_contents(__DIR__ . '/last_reference_no.txt');
if (!$referenceNo) {
    die("请先创建订单并获取订单号！\n");
}

// 清理订单号（去除空白字符和特殊字符）
$referenceNo = trim($referenceNo, " \t\n\r\0\x0B%");

// 获取跟踪单号参数
$params = [
    'reference_no' => $referenceNo    // 客户参考号
];

// 获取跟踪单号
$result = $app->getTrackingNumber($params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 * [data] => Array
 * (
 * [order_id] => 2948645
 * [refrence_no] => TEST1753775272
 * [shipping_method_no] => TEST1753775272
 * [channel_hawbcode] =>
 * [consignee_areacode] =>
 * [station_code] =>
 * )
 *
 * [success] => 1
 * [cnmessage] => 获取跟踪单号成功
 * [enmessage] => 获取跟踪单号成功
 * [order_id] => 0
 * )
 */

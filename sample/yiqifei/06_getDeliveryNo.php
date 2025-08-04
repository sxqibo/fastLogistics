<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 订单号列表
$orderNumbers = ['FEISZ1708169603YQ'];  // 替换为实际的订单号

// 获取派送单号和企业标签
$result = $app->getDeliveryNo($orderNumbers);

print_r($result);

/**
 * Array
 * (
 * [flag] => 1
 * [msg] =>
 * [pdfUrls] => Array
 * (
 * [0] => Array
 * (
 * [barcode] =>
 * [deliveryNumber] =>
 * [deliveryNumber2] =>
 * [deliveryNumbers] =>
 * [msg] =>
 * [orderNumber] => FEISZ1708169603YQ
 * [url] => http://api.17feia.com/label-internal/v1/label/internal/order/FEISZ1708169603YQ/pdf
 * )
 *
 * )
 *
 * )
 * === 获取派送单号和企业标签结果 ===
 * 获取成功！
 */
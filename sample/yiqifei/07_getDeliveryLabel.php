<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 订单号列表
$orderNumbers = ['FEISZ1708169603YQ'];  // 替换为实际的订单号

// 获取派送单号和派送标签
$result = $app->getDeliveryLabel($orderNumbers);

print_r($result);

/**
 * Array
 * (
 * [flag] =>
 * [msg] => 没有获取派送标签的权限
 * [pdfUrls] =>
 * )
 */
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

// 删除订单参数
$params = [
    'reference_no' => $referenceNo    // 客户参考号
];

// 删除订单
$result = $app->deleteOrder($params);
print_r($result);

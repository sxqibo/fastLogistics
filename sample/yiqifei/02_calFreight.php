<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 查询运费参数
$params = [
    'departureCode'   => 'SZ',         // 出发地（深圳代码）
    'destinationCode' => 'US',         // 目的国二字码
    'weight'          => 1.5,          // 重量(KG)
    'length'          => 30,           // 长(CM)
    'width'           => 20,           // 宽(CM)
    'height'          => 10,           // 高(CM)
    'productCode'     => 'USE13-MPKY', // 产品代码（美国空运免泡-普货）
];

// 查询运费, 说明：这个查询不出结果
$result = $app->calFreight($params);


print_r($result);

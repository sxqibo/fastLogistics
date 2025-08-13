<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 检查偏远地区参数
$params = [
    'productCode' => 'USE13-MPKY',    // 产品代码（美国空运免泡-普货）
    'country'     => 'US',            // 国家二字码
    'postCode'    => '10001',         // 邮编
    'weight'      => 2.0,             // 重量(KG)
];

// 检查是否是偏远地区
$result = $app->checkRemoteArea($params);


print_r($result);

/**
 * Array
 * (
 * [code] => POST03
 * [flag] => 1
 * [msg] => 偏远能派送，需收偏远费
 * [obj] => 60
 * [rows] => Array
 * (
 * )
 *
 * [total] => 0
 * )
 * === 偏远地区检查结果 ===
 * 状态: 成功
 * 结果: 偏远地区，可以派送，需收取偏远费
 * 偏远费: 60 USD
 *
 */
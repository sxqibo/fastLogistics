<?php

require_once '../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once './config.php';

// 初始化
$app = new Yiqifei($config);

// 计算费用参数
$params = [
    'productCode'    => 'USE13-MPKY',       // 产品代码 (美国空运免泡-普货)
    'productName'    => '美国空运免泡-普货',  // 产品名称
    'destinationCode' => 'US',               // 目的国二字码
    'referenceNo'    => 'TEST' . time(),     // 客户参考号
    'weight'         => 1.5,                 // 重量(KG)
    'length'         => 30,                  // 长(CM)
    'width'          => 20,                  // 宽(CM)
    'height'         => 10,                  // 高(CM)
    
    // 商品信息
    'goods' => [
        'nameEn'      => 'Test Product',     // 商品英文名
        'name'        => '测试产品',          // 商品中文名
        'quantity'    => 1,                  // 数量
        'value'       => 10.00,              // 申报价值
        'weight'      => 1.5,                // 重量
    ],
    
    // 收件人信息
    'recipient' => [
        'name'     => 'Test User',           // 收件人姓名
        'phone'    => '1234567890',          // 收件人电话
        'email'    => 'test@example.com',    // 收件人邮箱
        'address'  => '123 Test St',         // 收件人街道
        'city'     => 'New York',            // 收件人城市
        'state'    => 'NY',                  // 收件人州
        'postcode' => '10001',               // 收件人邮编
    ]
];

// 计算费用
$result = $app->calculatePrice($params);

print_r($result);

/**
 * Array
 * (
 * [code] =>
 * [flag] => 1
 * [msg] =>
 * [obj] =>
 * [rows] => Array
 * (
 * [0] => Array
 * (
 * [errorMsg] =>
 * [price] => 222
 * [referenceNo] => TEST1753770861
 * [status] => 1
 * )
 *
 * )
 *
 * [total] => 0
 * )
 */
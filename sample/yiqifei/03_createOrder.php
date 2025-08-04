<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 创建订单参数
$params = [
    'referenceNo'     => 'TEST' . time(),    // 客户参考号
    'productCode'     => 'USE13-MPKY',       // 产品代码 (美国空运免泡-普货)
    'countryCode'     => 'US',               // 目的国家二字码
    'weight'          => 1.5,                // 重量(KG)
    'length'          => 30,                 // 长(CM)
    'width'           => 20,                 // 宽(CM)
    'height'          => 10,                 // 高(CM)
    'salesPlatform'   => 'shopify',          // 销售平台（美国必填）
    
    // 收件人信息
    'recipientName'   => 'Test User',        // 收件人姓名
    'recipientPhone'  => '1234567890',       // 收件人电话
    'recipientEmail'  => 'test@example.com', // 收件人邮箱（美国必填）
    'recipientStreet' => '123 Test St',      // 收件人街道
    'recipientCity'   => 'New York',         // 收件人城市
    'recipientState'  => 'NY',               // 收件人州
    'recipientPostcode' => '10001',          // 收件人邮编
    
    // 包裹信息
    'items' => [
        [
            'description' => 'Test Product',  // 商品描述
            'quantity'    => 1,              // 数量
            'weight'      => 1.5,            // 重量
            'value'       => 10.00,          // 申报价值
            'purpose'     => 'SAMPLE',       // 商品用途（美国必填）
            'name'        => '测试产品',      // 商品中文名（必填）
            'nameEn'      => 'Test Product', // 商品英文名（必填）
        ]
    ],
];

// 创建订单
$result = $app->createOrder($params);

echo "=== 创建订单结果 ===\n";
print_r($result);


/**
 * === 创建订单结果 ===
 * Array
 * (
 * [fail] => 0
 * [failOrders] => Array
 * (
 * )
 *
 * [flag] => 1
 * [msg] =>
 * [returnProxyOrderNo] =>
 * [success] => 1
 * [successOrders] => Array
 * (
 * [0] => Array
 * (
 * [createProblem] =>
 * [createProblemDesc] =>
 * [deliveryCompany] => FEDEX-GROUND
 * [deliveryNumber] =>
 * [id] => 2c93ece19853a078019854cb99815a7b
 * [insideNumber] => FEISZ1708169603YQ
 * [pdfError] =>
 * [pdfPath] => http://api.17feia.com/label-internal/v1/label/internal/order/FEISZ1708169603YQ/pdf
 * [productId] => ff8080817548e998017549cc0f874241
 * [referenceNo] => TEST1753769284
 * [remoteFee] =>
 * [sheetApi] => 1
 * [supplyItemId] => 2c93ece68f9de066018fbcc512ab7ab3
 * [update] =>
 * )
 *
 * )
 *
 * [total] => 0
 * )
 *
 * 进程已结束，退出代码为 0
 */
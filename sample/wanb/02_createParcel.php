<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 创建包裹参数
$params = [
    'CustomerOrderNumber' => 'TEST' . time(),  // 客户订单号
    'ShippingMethod' => 'USPS',               // 运输方式代码
    'Weight' => 2.5,                          // 重量(KG)
    'Pieces' => 1,                            // 包裹件数
    'Warehouse' => 'SZ',                      // 仓库代码
    'InsuranceType' => 0,                     // 保险类型（0:不保险）
    'InsuranceAmount' => 0,                   // 保险金额
    'SourceCode' => 'API',                    // 来源代码
    'Sender' => [                             // 发件人信息
        'Name' => '张三',
        'Company' => '测试公司',
        'Phone' => '13800138000',
        'Email' => '',
        'Country' => 'CN',
        'Province' => '广东省',
        'City' => '深圳市',
        'Address1' => '福田区XX路XX号',
        'Address2' => '',
        'Postcode' => '518000'
    ],
    'Recipient' => [                          // 收件人信息
        'Name' => 'John Doe',
        'Company' => '',
        'Phone' => '1234567890',
        'Email' => 'john@example.com',
        'Country' => 'US',
        'Province' => 'CA',
        'City' => 'Los Angeles',
        'Address1' => '123 Main St',
        'Address2' => '',
        'Postcode' => '90001'
    ],
    'Items' => [                              // 包裹物品信息
        [
            'Name' => 'Dresses',              // 英文品名
            'NameLocal' => '连衣裙',          // 中文品名
            'Pieces' => 1,                    // 数量
            'UnitPrice' => 15.00,             // 单价(USD)
            'UnitWeight' => 0.5,              // 单重(KG)
            'Currency' => 'USD',              // 币种
            'HSCode' => '123456',             // 海关编码
            'Description' => 'Sample'          // 描述
        ]
    ]
];

// 创建包裹
$result = $app->createParcel($params);
print_r($result);

// 保存处理号，供后续接口使用
if ($result['Code'] === 0) {
    file_put_contents(__DIR__ . '/last_process_code.txt', $result['Data']['ProcessCode']);
}

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [ProcessCode] => WB12345678
 *             [CustomerOrderNumber] => TEST1234567890
 *             [TrackingNumber] => WB12345678US
 *             [Status] => Pending
 *             [CreatedTime] => 2024-01-29 15:30:00
 *         )
 * )
 */ 
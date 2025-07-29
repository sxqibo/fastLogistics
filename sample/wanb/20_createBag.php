<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 创建来货/揽收袋参数
$params = [
    'CustomerBagNumber' => 'BAG' . time(),  // 客户袋号
    'Warehouse' => 'SZ',                    // 仓库代码
    'Weight' => 10.5,                       // 重量(KG)
    'Length' => 50,                         // 长(CM)
    'Width' => 40,                          // 宽(CM)
    'Height' => 30,                         // 高(CM)
    'ExpectedArrivalTime' => '2024-02-01',  // 预计到仓时间
    'SourceCode' => 'API',                  // 来源代码
    'Parcels' => [                          // 包裹明细
        [
            'CustomerOrderNumber' => 'TEST' . time(),  // 客户订单号
            'ShippingMethod' => 'USPS',               // 运输方式代码
            'Weight' => 2.5,                          // 重量(KG)
            'Pieces' => 1,                            // 包裹件数
            'InsuranceType' => 0,                     // 保险类型（0:不保险）
            'InsuranceAmount' => 0,                   // 保险金额
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
        ]
        // 可以添加更多包裹
    ]
];

// 创建来货/揽收袋
$result = $app->createBag($params);
print_r($result);

// 保存袋号，供后续接口使用
if ($result['Code'] === 0) {
    file_put_contents(__DIR__ . '/last_bag_code.txt', $result['Data']['BagCode']);
}

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [BagCode] => SBXBAA0000038638YQ
 *             [CustomerBagNumber] => BAG1234567890
 *             [Status] => Pending
 *             [CreatedTime] => 2024-01-29 16:20:00
 *             [Parcels] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [ProcessCode] => SBXPAA0000038638YQ
 *                             [CustomerOrderNumber] => TEST1234567890
 *                             [Status] => Pending
 *                         )
 *                 )
 *         )
 * )
 */ 
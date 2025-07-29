<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 读取之前保存的袋号
$bagCode = @file_get_contents(__DIR__ . '/last_bag_code.txt');
if (!$bagCode) {
    die("请先创建来货/揽收袋并获取袋号！\n");
}

// 修改来货/揽收袋内包裹明细参数
$params = [
    'Parcels' => [                          // 包裹明细
        [
            'CustomerOrderNumber' => 'TEST' . time(),  // 客户订单号
            'ShippingMethod' => 'USPS',               // 运输方式代码
            'Weight' => 3.5,                          // 重量(KG)
            'Pieces' => 2,                            // 包裹件数
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
                    'Pieces' => 2,                    // 数量
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

// 修改来货/揽收袋内包裹明细
$result = $app->updateBagParcels($bagCode, $params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [BagCode] => SBXBAA0000038638YQ
 *             [UpdateTime] => 2024-01-29 16:30:00
 *             [Parcels] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [ProcessCode] => SBXPAA0000038639YQ
 *                             [CustomerOrderNumber] => TEST1234567891
 *                             [Status] => Pending
 *                         )
 *                 )
 *         )
 * )
 */ 
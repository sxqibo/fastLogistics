<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 创建包裹参数 - 按照万邦官方API文档
$params = [
    'ReferenceId' => 'TEST' . time(),         // 客户订单号，必须
    'ShippingMethod' => 'FDWUSEC-USPSLAX',               // 发货产品服务代码，必须
    'WeightInKg' => 1.0,                     // 包裹重量(KG)，必须
    'WarehouseCode' => 'SZ',                  // 交货仓库代码，必须
    'ShippingAddress' => [                    // 收件人地址信息，必须
        'Company' => '',
        'Street1' => '123 Main St',
        'Street2' => '',
        'Street3' => '',
        'City' => 'Los Angeles',
        'Province' => 'CA',
        'Country' => '',
        'CountryCode' => 'US',
        'Postcode' => '90001',
        'Contacter' => 'John Doe',
        'Tel' => '1234567890',
        'Email' => 'john@example.com',
        'TaxId' => ''
    ],
    'ItemDetails' => array(                   // 包裹件内明细，必须
        array(
            'GoodsId' => 'TEST001',
            'GoodsTitle' => 'Test Product',
            'DeclaredNameEn' => 'Dresses',
            'DeclaredNameCn' => '连衣裙',
            'DeclaredValue' => array(
                'Code' => 'USD',
                'Value' => 15.0
            ),
            'WeightInKg' => 1.0,
            'Quantity' => 1,
            'HSCode' => '123456',
            'CaseCode' => '',
            'SalesUrl' => '',
            'IsSensitive' => false,
            'Brand' => '',
            'Model' => '',
            'MaterialCn' => '',
            'MaterialEn' => '',
            'UsageCn' => '',
            'UsageEn' => '',
            'Manufacturer' => null
        )
    ),
    'TotalValue' => array(                    // 包裹总金额，必须
        'Code' => 'USD',
        'Value' => 15.0
    ),
    'TotalVolume' => array(                   // 包裹尺寸，必须
        'Length' => 20.0,
        'Width' => 15.0,
        'Height' => 10.0,
        'Unit' => 'CM'
    ),
    'WithBatteryType' => 'NOBattery',        // 包裹是否含有带电产品，必须
    'ItemType' => 'SPX',                      // 包裹类型，必须
    'TradeType' => 'B2C',                     // 订单交易类型，默认B2C
    'AllowRemoteArea' => true,                // 是否允许偏远区域下单
    'AutoConfirm' => false                    // 自动确认交运包裹
];

// 调试：打印参数
echo "发送的参数：\n";
print_r($params);

// 创建包裹
$result = $app->createParcel($params);
echo "\n返回结果：\n";
print_r($result);

// 保存处理号，供后续接口使用
if (isset($result['Code']) && $result['Code'] === 0) {
    file_put_contents(__DIR__ . '/last_process_code.txt', $result['Data']['ProcessCode']);
}


// 成功返回
// Array
// (
//     [Data] => Array
//         (
//             [ProcessCode] => SBXTT0000321705YQ
//             [IndexNumber] => 65140000321705
//             [ReferenceId] => TEST1754986879
//             [TrackingNumber] => 
//             [IsVirtualTrackingNumber] => 
//             [SortCode] => LAX
//             [IsRemoteArea] => 
//             [Status] => Original
//         )

//     [Succeeded] => 1
//     [Error] => 
// )
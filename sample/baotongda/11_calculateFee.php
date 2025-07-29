<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 费用试算参数
$params = [
    'shipping_method'  => 'PK0161',           // 运输方式代码
    'country_code'    => 'US',                // 目的国家二字代码
    'post_code'       => '02126',             // 目的地邮编
    'weight'          => '2.000',             // 重量（KG），字符串类型，最多3位小数
    'length'          => '30',                // 长度（CM）
    'width'           => '20',                // 宽度（CM）
    'height'          => '10',                // 高度（CM）
    'cargo_type'      => 'W',                 // 货物类型（W:包裹）
    'mail_cargo_type' => '2',                 // 包裹申报种类（2：CommercialSample 商品货样）
    'extra_service'   => [                    // 附加服务（可选）
        'insurance'   => '1',                 // 保险（0-不需要，1-需要）
        'pickup'      => '0',                 // 上门取件（0-不需要，1-需要）
        'deliver'     => '0'                  // 派送服务（0-不需要，1-需要）
    ]
];

echo "正在计算运费...\n\n";

// 费用试算
$result = $app->calculateFee($params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [success] => 1
 *     [cnmessage] => 费用试算成功
 *     [enmessage] => Calculate fee successfully
 *     [data] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [fee_kind_code] => E1
 *                     [fee_kind_name] => 速递运费
 *                     [currency_code] => RMB
 *                     [currency_name] => 人民币
 *                     [currency_rate] => 1.0000
 *                     [currency_amount] => 40.00
 *                     [amount] => 40.00
 *                     [remark] => 系统计费
 *                 )
 *         )
 * )
 */

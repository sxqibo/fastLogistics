<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 创建订单参数
$params = [
    'reference_no'     => 'TEST' . time(),    // 客户参考号
    'shipping_method'  => 'PK0161',           // 运输方式代码（使用文档中的示例代码）
    'order_weight'     => 2.0,                // 订单重量(KG)
    'order_pieces'     => 1,                  // 外包装件数
    'cargotype'        => 'W',                // 货物类型（W:包裹）
    'order_status'     => 'P',                // 订单状态（D:草稿）
    'mail_cargo_type'  => '2',                // 包裹申报种类（2：CommercialSample 商品货样）
    
    // 收件人信息
    'consignee' => [
        'consignee_company'    => '',                 // 公司名称（可选）
        'consignee_province'   => 'Massachusetts',    // 州/省
        'consignee_city'       => 'Mattapan',        // 城市
        'consignee_street'     => '760 Cummins HWY Apt11',  // 街道
        'consignee_postcode'   => '02126',           // 邮编
        'consignee_name'       => 'Marie Denise',    // 收件人姓名
        'consignee_telephone'  => '862-235-2637',    // 电话
        'consignee_mobile'     => '',                // 手机（可选）
        'consignee_countrycode'=> 'US'               // 国家二字码
    ],
    
    // 发件人信息
    'shipper' => [
        'shipper_countrycode' => 'CN',               // 国家二字码
        'shipper_city'       => '深圳',              // 城市
        'shipper_street'     => '福田区XX路XX号',    // 街道
        'shipper_name'       => '张三',              // 发件人姓名
        'shipper_telephone'  => '0755-12345678',     // 电话
        'shipper_mobile'     => '13800138000'        // 手机
    ],
    
    // 商品信息
    'invoice' => [
        [
            'invoice_enname'     => 'Dresses',       // 英文品名
            'invoice_cnname'     => '连衣裙',        // 中文品名
            'invoice_quantity'   => 1,               // 数量
            'unit_code'         => 'PCE',            // 单位（默认：PCE）
            'invoice_unitcharge' => 15.0,            // 单价
            'net_weight'        => 0.5,              // 重量
            'invoice_note'      => 'Sample'          // 备注（可选）
        ]
    ]
];

// 创建订单
$result = $app->createOrder($params);

// 保存订单号，供后续接口使用
if ($result['success']) {
    file_put_contents(__DIR__ . '/last_reference_no.txt', $params['reference_no']);
}

print_r($result);

/**
 * Array
 * (
 * [data] => Array
 * (
 * [order_id] => 2948603
 * [refrence_no] => TEST1753774070
 * [shipping_method_no] => TEST1753774070
 * [channel_hawbcode] =>
 * [consignee_areacode] =>
 * [station_code] =>
 * )
 *
 * [success] => 1
 * [cnmessage] => 订单创建成功
 * [enmessage] => 订单创建成功
 * [order_id] => 2948603
 * )
 */
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 1. 获取运输方式
echo "1. 获取运输方式列表：\n";
$result = $app->getBaseData([
    'method' => 'getshippingmethod'
]);
print_r($result);

echo "\n2. 获取客户运输方式列表：\n";
$result = $app->getBaseData([
    'method' => 'getcustomershippingmethod'
]);
print_r($result);

echo "\n3. 获取包裹申报种类：\n";
$result = $app->getBaseData([
    'method' => 'getmailcargotype'
]);
print_r($result);

echo "\n4. 获取国家列表：\n";
$result = $app->getBaseData([
    'method' => 'getcountry'
]);
print_r($result);

/**
 * 成功返回示例（运输方式）：
 * Array
 * (
 *     [success] => 1
 *     [cnmessage] => 获取运输方式成功
 *     [enmessage] => Get shipping methods successfully
 *     [data] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [shipping_method_code] => PK0161
 *                     [shipping_method_name] => 美国专线
 *                     [shipping_method_enname] => US Express
 *                     [status] => 1
 *                     [remark] => 普通包裹，时效5-7天
 *                 )
 *         )
 * )
 * 
 * 成功返回示例（包裹申报种类）：
 * Array
 * (
 *     [success] => 1
 *     [cnmessage] => 获取包裹申报种类成功
 *     [enmessage] => Get mail cargo types successfully
 *     [data] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [code] => 1
 *                     [name] => Gift
 *                     [cnname] => 礼品
 *                 )
 *             [1] => Array
 *                 (
 *                     [code] => 2
 *                     [name] => Sample
 *                     [cnname] => 货样
 *                 )
 *         )
 * )
 * 
 * 成功返回示例（国家列表）：
 * Array
 * (
 *     [success] => 1
 *     [cnmessage] => 获取国家列表成功
 *     [enmessage] => Get countries successfully
 *     [data] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [country_code] => US
 *                     [country_name] => United States
 *                     [country_cnname] => 美国
 *                     [status] => 1
 *                 )
 *         )
 * )
 */ 
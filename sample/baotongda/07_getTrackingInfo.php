<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 读取之前保存的订单号
$referenceNo = @file_get_contents(__DIR__ . '/last_reference_no.txt');
if (!$referenceNo) {
    die("请先创建订单并获取订单号！\n");
}

// 清理订单号（去除空白字符和特殊字符）
$referenceNo = trim($referenceNo, " \t\n\r\0\x0B%");

// 先获取跟踪单号
echo "正在获取跟踪单号...\n";
$trackResult = $app->getTrackingNumber(['reference_no' => $referenceNo]);
print_r($trackResult);

if (!$trackResult['success']) {
    die("获取跟踪单号失败：" . $trackResult['cnmessage'] . "\n");
}

if (empty($trackResult['data']['shipping_method_no'])) {
    die("订单 {$referenceNo} 还未分配跟踪单号，请等待系统分配后再查询。\n");
}

$trackingNumber = $trackResult['data']['shipping_method_no'];
echo "\n使用跟踪单号 {$trackingNumber} 查询跟踪记录...\n\n";

// 获取跟踪记录参数
$params = [
    'tracking_number' => $trackingNumber    // 服务商单号
];

// 获取跟踪记录
$result = $app->getTrackingInfo($params);
print_r($result);



//  正在获取跟踪单号...
// Array
// (
//     [data] => Array
//         (
//             [order_id] => 2976489
//             [refrence_no] => 111-1442158-9430647-10
//             [shipping_method_no] => BTDBY0007275333YQ
//             [channel_hawbcode] => RG031939098CN
//             [consignee_areacode] => 
//             [station_code] => 
//         )

//     [success] => 1
//     [cnmessage] => 获取跟踪单号成功
//     [enmessage] => 获取跟踪单号成功
//     [order_id] => 0
// )

// 使用跟踪单号 BTDBY0007275333YQ 查询跟踪记录...

// Array
// (
//     [data] => Array
//         (
//             [0] => Array
//                 (
//                     [shipper_hawbcode] => 111-1442158-9430647-10
//                     [server_hawbcode] => BTDBY0007275333YQ
//                     [channel_hawbcode] => RG031939098CN
//                     [destination_country] => US
//                     [destination_country_name] => 
//                     [track_status] => NT
//                     [track_status_name] => 转运中
//                     [signatory_name] => 
//                     [details] => Array
//                         (
//                             [0] => Array
//                                 (
//                                     [tbs_id] => 3415485
//                                     [track_occur_date] => 2025-08-12 16:18:45
//                                     [track_location] => 
//                                     [track_description] => Shipment information received
//                                     [track_description_en] => 快件电子信息已经收到
//                                     [track_code] => IR
//                                     [track_status] => NT
//                                     [track_status_cnname] => 转运中
//                                 )

//                         )

//                 )

//         )

//     [success] => 1
//     [cnmessage] => 获取跟踪记录成功
//     [enmessage] => 获取跟踪记录成功
//     [order_id] => 0
// )
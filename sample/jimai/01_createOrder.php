<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__.'/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];

$data     = new Jimai($clientId, $token);

/**
 * 01、 创建快件订单,仓储订单,快递制单
 */

//step1:订单
$orderNo     = time();  //客户订单号
$channelCode = 'JM160';  //运输方式代码(三种物流的方式是不一样的，一定要填对应的物流方式，否则会出错)

//step2:收件人
$rCountryCode = 'FR';           //收件人所在国家
$rName        = 'madi IDAROUSSE';          //收件人姓
$rAddress1    = '29 rue Michel ange apt C01';          //收件人详细地址1
$rAddress2    = '';          //收件人详细地址2
$rCity        = 'Toulouse';     //收件人所在城市
$rProvince    = '';             //收件人所在省
$rCode        = '31200';       //发件人邮编,必填项,5位数字
$rMobile      = '0769288353';  //发件人手机
$remarks      = '备注'; //订单备注，用于打印配货单

//step3:商品
$goods = [
    [
        'goods_cn_name'       => '尾翼',
        'goods_en_name'       => 'tail unit',
        'goods_number'        => 2,          //申报数量,商品数量
        'goods_single_worth'  => 3,          //单个产品的申报价值
        'goods_single_weight' => 1,          //运单包裹的件数
        'goods_customs_code'  => '',         //海关编码
    ],
    [
        'goods_cn_name'       => '商品2',
        'goods_en_name'       => 'shangpin2',
        'goods_number'        => 3,          //申报数量,商品数量
        'goods_single_worth'  => 3,          //单个产品的申报价值
        'goods_single_weight' => 1,          //运单包裹的件数
        'goods_customs_code'  => '',         //海关编码
    ],
];

// step4:
$iossNumber = 'IM4420001405';

$result = $data->createOrder(
    $orderNo, $channelCode,
    $rCountryCode, $rName, $rAddress1, $rAddress2, $rCity, $rProvince, $rCode, $rMobile,
    $goods, $iossNumber, $remarks, 1);


print_r($result);

// 返回成功
// Array
// (
//     [code] => 200
//     [content] => Array
//         (
//             [statusCode] => success
//             [returnDatas] => Array
//                 (
//                     [0] => Array
//                         (
//                             [statusCode] => success
//                             [trackNumber] => YT2523200703023622
//                             [corpBillid] => JM5082025520YQ
//                             [customerNumber] => 1755660562
//                             [childTrackingNumber] => 
//                             [shipmentid] => 
//                             [labelBillid] => 
//                             [message] => 
//                         )

//                 )

//         )

// )


// 返回失败
// Array
// (
//     [code] => 200
//     [content] => Array
//         (
//             [statusCode] => success
//             [message] => 客户订单号:1755660468,异常:客户订单号：1755660468,获取单号失败： 订单【JM5082025515YQ】  提交代理获取单号失败：订单:JM5082025515YQ 提交异常信息：创建订单代理返回错误信息 :提交失败! ,IOSS号 IM4420001201 未备案，请先备案,
//             [returnDatas] => Array
//                 (
//                     [0] => Array
//                         (
//                             [statusCode] => error
//                             [corpBillid] => JM5082025515YQ
//                             [customerNumber] => 1755660468
//                             [childTrackingNumber] => 
//                             [shipmentid] => 
//                             [labelBillid] => 
//                             [message] => 客户订单号：1755660468,获取单号失败： 订单【JM5082025515YQ】  提交代理获取单号失败：订单:JM5082025515YQ 提交异常信息：创建订单代理返回错误信息 :提交失败! ,IOSS号 IM4420001201 未备案，请先备案
//                         )

//                 )

//         )

// )
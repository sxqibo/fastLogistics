<?php

use Sxqibo\Logistics\Yuntu;

require_once '../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);

/**
 * 07.运单申请
 */
//step1:订单
$orderNo     = time();   //客户订单号
$channelCode = 'THZXR';  //运输方式代码(三种物流的方式是不一样的，一定要填对应的物流方式，否则会出错)
$totalValue  = 100;      //云途不用填,填上也没关系

//step2:收件人
$rCountryCode = 'DE';           //收件人所在国家
$rName        = 'Juan';          //收件人姓
$rAddress     = 'August-cueni-strasse 5';          //收件人详细地址
$rCity        = 'Zwingen';     //收件人所在城市
$rProvince    = 'Zwingen';             //收件人所在省
$rCode        = '04222';       //发件人邮编,必填项,5位数字
$rMobile      = '18803415820';  //发件人手机

//step3:商品
$goods = [
    [
        'goods_cn_name'       => '商品1',
        'goods_en_name'       => 'shangpin1',
        'goods_number'        => 2,    //申报数量,商品数量
        'goods_single_worth'  => 3,    //单个产品的申报价值
        'goods_single_weight' => 1,    //运单包裹的件数
        'goods_currency_code' => 'USD', //币种（云途需要）
        'goods_hsCode'        => '01041010', //海关（三态需要）
    ],
    [
        'goods_cn_name'       => '商品2',
        'goods_en_name'       => 'shangpin2',
        'goods_number'        => 3,    //申报数量,商品数量
        'goods_single_worth'  => 3,    //单个产品的申报价值
        'goods_single_weight' => 1,    //运单包裹的件数
        'goods_currency_code' => 'USD', //币种（云途需要）
        'goods_hsCode'        => '01041010', //海关（三态需要）
    ],
];

$result = $data->createOrder(
    $orderNo, $channelCode,
    $rCountryCode, $rName, $rAddress, $rCity, $rProvince, $rCode, $rMobile,
    $goods);


print_r($result);



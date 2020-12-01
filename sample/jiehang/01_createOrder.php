<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

/**
 * 01、 创建快件订单,仓储订单,快递制单
 */
//物流信息
//$channelCode = 'GNPS';  // 渠道代码
//$countryCode = 'GE';
//
////人的信息
//$name     = 'hongwei';
//$address  = 'taiyuan';
//$mobile   = '18903467858';
//$province = 'shanxi';
//$city     = 'taiyuan';
//$postCode = '030001';
//
////产品信息
//$orderNo    = time();
//$totalValue = 100;
//$number     = 1;
//$cnname     = 'LHNLY餐厅';
//$enname     = 'LHNLY-Esszimmerstüh';
//$price      = 100.00;
//$weight     = 5.1;
//
//$result = $data->createOrder($orderNo, $channelCode, $countryCode, $totalValue, $number,
//    $name, $address, $mobile, $province, $city, $postCode, $cnname, $enname, $price, $weight);
//print_r($result);

//step1:订单
$orderNo     = time();  //客户订单号
$channelCode = 'GNPS';  //运输方式代码(三种物流的方式是不一样的，一定要填对应的物流方式，否则会出错)
$totalValue  = 100;

//step2:收件人
$rCountryCode = 'DE';           //收件人所在国家
$rName        = 'Juan';          //收件人姓
$rAddress     = 'August-cueni-strasse 5';          //收件人详细地址
$rCity        = 'Zwingen';     //收件人所在城市
$rProvince    = 'Zwingen';             //收件人所在省
$rCode        = '04222';       //发件人邮编,必填项,5位数字
$rMobile      = '18803415820';  //发件人手机

//step3:商品
$goods  = [
    [
        'goods_cn_name'       => '商品1',
        'goods_en_name'       => 'shangpin1',
        'goods_number'        => 2,          //申报数量,商品数量
        'goods_single_weight' => 1,          //运单包裹的件数
        'goods_currency_code' => 'USD',      //币种（云途需要）
        'goods_hsCode'        => '01041010', //海关（三态需要）
    ],
    [
        'goods_cn_name'       => '商品2',
        'goods_en_name'       => 'shangpin2',
        'goods_number'        => 3,          //申报数量,商品数量
        'goods_single_weight' => 1,          //运单包裹的件数
        'goods_currency_code' => 'USD',      //币种（云途需要）
        'goods_hsCode'        => '01041010', //海关（三态需要）
    ],
];
$result = $data->createOrder(
    $orderNo, $channelCode, $totalValue,
    $rCountryCode, $rName, $rAddress, $rCity, $rProvince, $rCode, $rMobile,
    $goods);


print_r($result);


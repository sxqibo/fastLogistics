<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Jiehang($clientId, $token);


/**
 * 02、 修改快件订单,仓储订单,快递制单
 */
//物流信息
$channelCode = 'GNPS';  // 渠道代码
$countryCode = 'GE';

//人的信息
$name        = 'hongwei';
$address     = 'taiyuan';
$mobile      = '18903467858';
$province    = 'shanxi';
$city        = 'taiyuan';
$postCode    = '030001';

//产品信息
$orderNo     = time();
$totalValue  = 100;
$number      = 1;
$cnname      = 'LHNLY餐厅';
$enname      = 'LHNLY-Esszimmerstüh';
$price       = 100.00;
$weight      = 5.1;

$result = $data->createOrder($orderNo, $channelCode, $countryCode, $totalValue, $number,
    $name, $address, $mobile, $province, $city, $postCode, $cnname, $enname, $price, $weight);
print_r($result);


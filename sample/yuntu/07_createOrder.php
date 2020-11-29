<?php

use Sxqibo\Logistics\Yuntu;

require '../vendor/autoload.php';
require './config.php';

$data = new Yuntu($code, $apiSecret);

/**
 * 05.查询跟踪号
 */
//step1:订单
$orderNo       = time();  //客户订单号
$channelCode   = 'THZXR';  //运输方式代码
$packageNumber = 1; //运单包裹的件数

//step2:收件人
$receiverCountryCode = 'DE'; //收件人所在国家
$receiverName        = 'xin'; //收件人姓
$receiverAddress     = 'xin'; //收件人详细地址
$receiverCity        = 'Lockwook'; //收件人所在城市
$receiverPostCode    = 'Lockwook'; //发件人邮编

//step3:商品
$goodsCnName       = '商品1';
$goodsEnName       = 'shangpin1';
$goodsSinglePrice  = 10;
$goodsNumber       = 1;     //申报数量,商品数量
$goodsSingleWeight = 1;     //运单包裹的件数

$result = $data->createOrder($orderNo, $channelCode, $packageNumber,
    $receiverCountryCode, $receiverName, $receiverAddress, $receiverCity, $receiverPostCode,
    $goodsCnName, $goodsEnName, $goodsNumber, $goodsSinglePrice, $goodsSingleWeight);

print_r($result);



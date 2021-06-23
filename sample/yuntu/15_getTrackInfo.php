<?php
use Sxqibo\Logistics\Yuntu;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);

/**
 * 15.查询跟踪号
 *
 * （一）订单信息： https://sellercentral.amazon.co.uk/orders-v3/order/303-1190716-4253118

 * 订单编号：# 303-1190716-4253118
 * ASIN: B07QLX2HSM
 * SKU: B07QLX2HSM_4
 * 商品编号: 1110X3A5W1F
 * 订单商品编号: 35010295275491
 * 发货日期: 2020年11月14日周六
 * 承运人: Yun Express
 * 追踪编码: YT2032321266071730
 *
 * （二）以下是云途查询到的
 *
 * 云快车追踪号码: YT2032321266071730
 * 最后一英里交货跟踪编号: H1002530848293901029
 *
 */

$orderNo = "YT2027421266039305";
$result  = $data->getTrackInfo($orderNo);
print_r($result);

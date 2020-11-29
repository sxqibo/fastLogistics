<?php
use Sxqibo\Logistics\Yuntu;

require_once '../vendor/autoload.php';
require_once './config.php';

$data = new Yuntu($code, $apiSecret);

/**
 * 08.查询运单
 * $orderNo 可以是 本地订单号 或 运单号
 * 本地订单号 42259-47
 * 运单号 YT2031721266061991
 */

//$orderNo = "YT2031721266061991";  //运单号
$orderNo = "42259-47";              //本地订单号

$result = $data->getOrder($orderNo);
print_r($result);

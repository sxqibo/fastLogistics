<?php
use Sxqibo\Logistics\Yuntu;

require_once '../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);

/**
 * 09.修改订单预报重量
 * $orderNo 可以是 本地订单号 或 运单号
 * 本地订单号 42259-47
 * 运单号 YT2031721266061991
 */

//$orderNo = "YT2031721266061991";  //运单号
$orderNo = "42259-47";              //本地订单号
$weight  = 1.22;                    //重量，待测试,正式用时用下边的11去掉，暂时放的，以防误操作

$result = $data->updateWeight($orderNo.'11', $weight);
print_r($result);

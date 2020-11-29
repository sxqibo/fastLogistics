<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require 'config.php';

$data   = new Santai($appKey, $token, $userId);

/**
 * 03、通过订单号获取费用
 * @doc https://www.sfcservice.com/api-doc
 */
$param  = [  //8个必填,商品的3个必填


];
$result = $data->getFeeByOrderCode($param);
print_r($result);


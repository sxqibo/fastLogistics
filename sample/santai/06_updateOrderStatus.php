<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__ . '/config.php';

$appKey = $config['sanTai']['appKey'];
$token  = $config['sanTai']['token'];
$userId = $config['sanTai']['userId'];
$data   = new Santai($appKey, $token, $userId);

/**
 *  07、通过订单号获取费用 getFeeByOrderCode
 * @doc https://www.sfcservice.com/api-doc
 */
$orderNo = 'SFC2WW4159011070015';
$orderStatus = 'preprocess';
$result    = $data->updateOrderStatus($orderNo, $orderStatus);
print_r($result);


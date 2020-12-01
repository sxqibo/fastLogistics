<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require_once '../config.php';

$appKey = $config['sanTai']['appKey'];
$token  = $config['sanTai']['token'];
$userId = $config['sanTai']['userId'];
$data   = new Santai($appKey, $token, $userId);

/**
 * 12、获取批量订单跟踪信息
 * 说明：接口有这个，但实际上没有这个接口，垃圾接口文档
 *
 * @doc https://www.sfcservice.com/api-doc
 */
$orderNo1 = 'SFC2WW4159011070015';
//$orderNo2 = '';
$param    = [$orderNo1];
$result   = $data->batchGetTrackingInfo($param);
print_r($result);


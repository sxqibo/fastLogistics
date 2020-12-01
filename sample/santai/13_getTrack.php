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
 *
 * @doc https://www.sfcservice.com/api-doc
 */
$orderNo1 = 'SFC2WW4159011070015';
//$orderNo2 = '';
$param    = [$orderNo1];
$result   = $data->getTrack($param);
print_r($result);


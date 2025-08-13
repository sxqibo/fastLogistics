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
 * 10、获取时间段订单信息
 *
 * @doc https://www.sfcservice.com/api-doc
 */
$startTime = '2020-11-20 09:00:00';
$endTime   = '2020-11-21 18:00:00';
$result    = $data->getFeeByTime($startTime, $endTime);
print_r($result);


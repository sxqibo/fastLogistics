<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Santai($appKey, $token, $userId);

/**
 * 10、获取时间段订单信息
 *
 * @doc https://www.sfcservice.com/api-doc
 */
$startTime = '2020-11-20 09:00:00';
$endTime   = '2020-11-21 18:00:00';
$result    = $data->getFeeByTime($startTime, $endTime);
print_r($result);


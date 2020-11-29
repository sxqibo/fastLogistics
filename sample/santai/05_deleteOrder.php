<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require 'config.php';

$data   = new Santai($appKey, $token, $userId);

/**
 * 05、删除订单 (deleteOrder)
 * @doc https://www.sfcservice.com/api-doc
 */
$orderNo = 'SFC2WW4159011230004';
$result    = $data->deleteOrder($orderNo);
print_r($result);


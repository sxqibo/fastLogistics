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
 * 05、删除订单 (deleteOrder)
 * @doc https://www.sfcservice.com/api-doc
 */
$orderNo = 'SFC2WW4159011230004';
$result    = $data->deleteOrder($orderNo);
print_r($result);


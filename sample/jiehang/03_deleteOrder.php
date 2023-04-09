<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

/**
 * 03、 删除快件订单,仓储订单,快递制单
 */
$corpBillid      = 'JHLCN0112395391YQ';

$result = $data->deleteOrder($corpBillid);
print_r($result);


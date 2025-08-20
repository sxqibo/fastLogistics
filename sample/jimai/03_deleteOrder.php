<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__.'/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 03、 删除快件订单,仓储订单,快递制单
 */
$corpBillid      = 'JHLCN0112395391YQ';

$result = $data->deleteOrder($corpBillid);
print_r($result);


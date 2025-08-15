<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 04、 根据公司单号提取转单号
 */
$corpBillid      = 'JM5081123173YQ';

$result = $data->searchOrderTracknumber($corpBillid);
print_r($result);


<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 04、 根据公司单号提取转单号
 */
$corpBillid      = 'JHLCN0111790183YQ';

$result = $data->searchOrderTracknumber($corpBillid);
print_r($result);


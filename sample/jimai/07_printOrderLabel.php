<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__.'/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);
/**
 * 07、 打印地址标签
 */
$corpBillid      = 'JM5082025520YQ';
$result = $data->printOrderLabel($corpBillid);
print_r($result);


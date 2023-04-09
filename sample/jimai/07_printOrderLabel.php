<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);
/**
 * 07、 打印地址标签
 */
$corpBillid      = 'CN0110681773SZ';
$result = $data->printOrderLabel($corpBillid);
print_r($result);


<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Jiehang($clientId, $token);

/**
 * 07、 打印地址标签
 */
$corpBillid      = 'CN0110681773SZ';
$result = $data->printOrderLabel($corpBillid);
print_r($result);


<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

/**
 * 09、 查询保险类型
 */
$result = $data->searchInsuranceType();
print_r($result);


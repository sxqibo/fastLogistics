<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

/**
 * 05、 查询启用的仓库
 */
$result = $data->searchStartHouse();
print_r($result);


<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

//06、 查询启用的入仓渠道 （目前在用，栏目：物流公司-运输方式）
$result = $data->getShipTypes();
print_r($result);


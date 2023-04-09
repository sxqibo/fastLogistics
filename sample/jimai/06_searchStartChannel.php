<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

//06、 查询启用的入仓渠道 （目前在用，栏目：物流公司-运输方式）
$result = $data->getShipTypes();
print_r($result);


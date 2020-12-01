<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

//10、 查价格 （目前在用，栏目：物流公司-物流优选）
$countryCode = 'JP';
$weight      = 5;

$result = $data->getPrice($countryCode, $weight);

print_r($result);

<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__.'/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

//10、 查价格 （目前在用，栏目：物流公司-物流优选）
$countryCode = 'JP';
$weight      = 5;

$result = $data->getPrice($countryCode, $weight);

print_r($result);

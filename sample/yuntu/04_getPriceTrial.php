<?php

use Sxqibo\Logistics\Yuntu;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);

//04.查询价格（对应栏目：物流公司-物流优选）
$result = $data->getPrice('GE', 1);  //在用，两个参数必传
print_r($result);

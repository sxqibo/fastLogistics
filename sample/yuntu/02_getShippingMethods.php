<?php
use Sxqibo\Logistics\Yuntu;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);

//02.查询运输方式
$result = $data->getShipTypes('DE');  //在用，带国家
//$result = $data->getShipTypes();  //在用，不带国家
print_r($result);


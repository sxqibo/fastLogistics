<?php

use Sxqibo\Logistics\Yuntu;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);

//01.查询国家简码
$result = $data->getCountry();

print_r($result);


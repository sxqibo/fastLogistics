<?php
use Sxqibo\Logistics\Yuntu;

require_once '../vendor/autoload.php';
require_once './config.php';

$data = new Yuntu($code, $apiSecret);

//03.查询货品类型
$result = $data->getGoodsType();
print_r($result);


<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Jiehang($clientId, $token);

/**
 * 09、 查询保险类型
 */
$result = $data->searchInsuranceType();
print_r($result);


<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

/**
 * 11、 查轨迹
 */
$trackNumber = '564654858493';

$result = $data->getTrack($trackNumber);
print_r($result);


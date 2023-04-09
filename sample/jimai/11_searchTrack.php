<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 11、 查轨迹
 */
$trackNumber = '564654858493';

$result = $data->getTrack($trackNumber);
print_r($result);


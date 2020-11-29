<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Jiehang($clientId, $token);

/**
 * 11、 查轨迹
 */
$trackNumber = '564654858493';

$result = $data->searchTrack($trackNumber);
print_r($result);


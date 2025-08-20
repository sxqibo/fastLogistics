<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 11、 查轨迹
 */
$trackNumber = 'JM5081123173YQ';

$result = $data->getTrack($trackNumber);
print_r($result);


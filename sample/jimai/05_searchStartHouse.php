<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once __DIR__.'/../../vendor/autoload.php';
$config = require_once __DIR__.'/config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 05、 查询启用的仓库
 */
$result = $data->searchStartHouse();
print_r($result);


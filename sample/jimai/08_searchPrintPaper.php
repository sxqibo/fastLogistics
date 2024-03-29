<?php
//命名空间
use Sxqibo\Logistics\Jimai;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jimai']['clientId'];
$token    = $config['jimai']['token'];
$data     = new Jimai($clientId, $token);

/**
 * 08、 根据渠道查询支持的打印纸张
 */
$channelCode      = 'JHLCN0112295007YQ';
$result = $data->searchPrintPaper($channelCode);
print_r($result);


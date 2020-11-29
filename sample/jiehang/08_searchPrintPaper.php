<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Jiehang($clientId, $token);

/**
 * 08、 根据渠道查询支持的打印纸张
 */
$channelCode      = 'JHLCN0112295007YQ';
$result = $data->searchPrintPaper($channelCode);
print_r($result);

